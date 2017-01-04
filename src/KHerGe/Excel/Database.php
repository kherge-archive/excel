<?php

namespace KHerGe\Excel;

use Exception;
use Generator;
use KHerGe\Excel\Exception\Database\CouldNotBeginTransactionException;
use KHerGe\Excel\Exception\Database\CouldNotCommitTransactionException;
use KHerGe\Excel\Exception\Database\CouldNotExecuteException;
use KHerGe\Excel\Exception\Database\CouldNotPrepareException;
use KHerGe\Excel\Exception\Database\CouldNotRollBackTransactionException;
use KHerGe\Excel\Exception\Database\NoSuchColumnException;
use KHerGe\Excel\Exception\Database\PreparedStatementInUseException;
use PDO;
use PDOException;
use PDOStatement;
use SplObjectStorage;

use function KHerGe\File\remove;
use function KHerGe\File\temp_file;

/**
 * Manages a temporary SQLite database.
 *
 * This class will create a new temporary SQLite database on instantiation.
 * When the instance is destructed, the database will be automatically deleted.
 * The class also provides a set of helper methods in order to simplify the use
 * of the database and to improve performance.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Database
{
    /**
     * The list of keywords that cause the schema to change.
     *
     * @var string[]
     */
    private static $changes = [
        'CREATE',
        'DROP'
    ];

    /**
     * The path to the database file.
     *
     * @var string
     */
    private $file;

    /**
     * The database connection.
     *
     * @var PDO
     */
    private $pdo;

    /**
     * The prepared statements.
     *
     * @var PDOStatement[]
     */
    private $prepared = [];

    /**
     * The prepared statements in use.
     *
     * @var PDOStatement[]|SplObjectStorage
     */
    private $preparedInUse;

    /**
     * Initializes the new database manager.
     *
     * ```php
     * $database = new Database('/path/to/temp.db', $pdo);
     * ```
     *
     * @param string $file The path to the temporary file.
     * @param PDO    $pdo  The database connection.
     */
    public function __construct($file, PDO $pdo)
    {
        $this->file = $file;
        $this->pdo = $pdo;

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(
            PDO::ATTR_DEFAULT_FETCH_MODE,
            PDO::FETCH_ASSOC
        );

        $this->pdo->exec('PRAGMA foreign_keys = 1');

        $this->preparedInUse = new SplObjectStorage();
    }

    /**
     * Deletes the temporary database.
     */
    public function __destruct()
    {
        $this->pdo = null;

        remove($this->file);
    }

    /**
     * Executes a statement and returns all of the values for all rows.
     *
     * This method will return all of the values for all of the rows in the
     * result set for the executed statement. If a `$key` is provided, the
     * array key for each element will be the value of that field. If a
     * `$value` is also provided, the value of that column will only be
     * returned with the key.
     *
     * ```php
     * $rows = $database->all('SELECT * FROM example');
     *
     * $pairs = $database->all(
     *     'SELECT * FROM example',
     *     [],
     *     'id',
     *     'name'
     * );
     * ```
     *
     * @param string      $statement  The statement to prepare.
     * @param array       $parameters The parameters for the statement.
     * @param null|string $key        The column to use as the array key.
     * @param null|string $value      The column to use as the array value.
     *
     * @return array The row values.
     *
     * @throws NoSuchColumnException If a key or value column does not exist.
     */
    public function all(
        $statement,
        array $parameters = [],
        $key = null,
        $value = null
    ) {
        $executed = $this->execute($statement, $parameters);

        if (null === $key) {
            $rows = $executed->fetchAll();

            $this->release($executed);

            return $rows;
        }

        $rows = [];

        while (false !== ($row = $executed->fetch())) {
            if (!array_key_exists($key, $row)) {
                throw new NoSuchColumnException(
                    'The column "%s" is used as a key but does not exist.',
                    $key
                );
            }

            if (null === $value) {
                $rows[$row[$key]] = $row;
            } elseif (!array_key_exists($value, $row)) {
                throw new NoSuchColumnException(
                    'The column "%s" is used as a value but does not exist.',
                    $value
                );
            } else {
                $rows[$row[$key]] = $row[$value];
            }
        }

        $this->release($executed);

        return $rows;
    }

    /**
     * Begins a new transaction.
     *
     * ```php
     * $database->begin();
     * ```
     *
     * @throws CouldNotBeginTransactionException If the transaction could not begin.
     */
    public function begin()
    {
        try {
            $this->release($this->execute('BEGIN TRANSACTION'));
        } catch (CouldNotExecuteException $exception) {
            throw new CouldNotBeginTransactionException(
                'The transaction could not begin.',
                $exception
            );
        }
    }

    /**
     * Executes a statement and returns the value of the first column.
     *
     * This method will prepare and execute the given statement using the
     * `execute()` method. Once executed, the value of the first column of
     * the first row is returned.
     *
     * ```php
     * $column = $database->column('SELECT COUNT(*) FROM example');
     * ```
     *
     * @param string $statement  The statement to prepare.
     * @param array  $parameters The parameters for the statement.
     *
     * @return null|string The column value, if any.
     */
    public function column($statement, array $parameters = [])
    {
        $executed = $this->execute($statement, $parameters);
        $column = $executed->fetchColumn();

        $this->release($executed);

        return (false === $column) ? null : $column;
    }

    /**
     * Commits the current transaction.
     *
     * ```php
     * $database->commit();
     * ```
     *
     * @throws CouldNotCommitTransactionException If the transaction could not be committed.
     */
    public function commit()
    {
        try {
            $this->release($this->execute('COMMIT'));
        } catch (CouldNotExecuteException $exception) {
            throw new CouldNotCommitTransactionException(
                'The transaction could not committed.',
                $exception
            );
        }
    }

    /**
     * Creates a new temporary database.
     *
     * @return Database The new database manager.
     */
    public static function create()
    {
        $file = temp_file();

        return new self($file, new PDO("sqlite:$file"));
    }

    /**
     * Prepares a statement and executes it.
     *
     * This method will prepare the given SQL statement using the `prepare()`
     * method. Once prepared, the statement is executed using the parameters
     * provided. If the statement could not be executed, an exception is
     * thrown.
     *
     * ```php
     * $executed = $database->execute(
     *     'SELECT * FROM example WHERE id = :id',
     *     [
     *         'id' => 123
     *     ]
     * );
     *
     * // Do work with the executed statement.
     *
     * $database->release($executed);
     * ```
     *
     * @param string $statement  The statement to prepare.
     * @param array  $parameters The parameters for the statement.
     *
     * @return PDOStatement The executed statement.
     *
     * @throws CouldNotExecuteException If the statement could not be executed.
     * @throws CouldNotPrepareException If the statement could not be prepared.
     */
    public function execute($statement, array $parameters = [])
    {
        $prepared = $this->prepare($statement);

        try {
            $prepared->execute($parameters);
        } catch (PDOException $exception) {
            throw new CouldNotExecuteException(
                'The SQL statement "%s" could not be executed with: %s',
                $statement,
                function_exists('json_encode')
                    ? json_encode($parameters)
                    : serialize($parameters),
                $exception
            );
        }

        return $prepared;
    }

    /**
     * Executes a statement and yields each row in the result set.
     *
     * This method will prepare and execute the given statement using the
     * `execute()` method. Each row in the results set will be yielded.
     *
     * ```php
     * $generator = $database->iterate('SELECT * FROM example');
     *
     * foreach ($generator as $row) {
     *     // Do something with the row.
     * }
     * ```
     *
     * @param string $statement  The statement to prepare.
     * @param array  $parameters The parameters for the statement.
     *
     * @return array|Generator The row values.
     *
     * @throws NoSuchColumnException If a key or value column does not exist.
     */
    public function iterate($statement, array $parameters = [])
    {
        $executed = $this->execute($statement, $parameters);

        try {
            while (false !== ($row = $executed->fetch())) {
                yield $row;
            }
        } finally {
            $this->release($executed);
        }
    }

    /**
     * Prepares a statement for execute.
     *
     * This method will prepare the given SQL statement and cache the resulting
     * object. When the same statement is prepared again, the same object will
     * be returned. If the statement was prepared before but is currently in
     * use, an exception is thrown. Once you have finished using the prepared
     * statement, call `release()` to allow the statement to be re-used.
     *
     * ```php
     * $prepared = $database->prepare('SELECT * FROM example');
     *
     * $again = $database->prepare('SELECT * FROM example');
     *
     * if ($prepared === $again) {
     *     // The prepared statements are the same.
     * }
     * ```
     *
     * @param string $statement The statement to prepare.
     *
     * @return PDOStatement The prepared statement.
     *
     * @throws CouldNotPrepareException        If the statement could not be prepared.
     * @throws PreparedStatementInUseException If the statement is already in use.
     */
    public function prepare($statement)
    {
        $this->clear($statement);

        if (!isset($this->prepared[$statement])) {
            try {
                $this->prepared[$statement] = $this->pdo->prepare($statement);
            } catch (PDOException $exception) {
                throw new CouldNotPrepareException(
                    'The SQL statement "%s" could not be prepared.',
                    $statement,
                    $exception
                );
            }
        }

        if (isset($this->preparedInUse[$this->prepared[$statement]])) {
            throw new PreparedStatementInUseException(
                'The prepared SQL statement "%s" is already in use.',
                $statement
            );
        }

        $this->preparedInUse[$this->prepared[$statement]] = true;

        return $this->prepared[$statement];
    }

    /**
     * Releases a prepared statement so that it can be re-used.
     *
     * This method will release a statement that was prepared using the
     * `prepare()` method. This "release" the statement so that another
     * process can re-use it.
     *
     * ```php
     * $prepared = $database->prepare('SELECT * FROM example');
     *
     * // Do work with the prepared statement.
     *
     * $database->release($prepared);
     * ```
     *
     * @param PDOStatement $statement The prepared statement.
     */
    public function release(PDOStatement $statement)
    {
        $statement->closeCursor();

        unset($this->preparedInUse[$statement]);
    }

    /**
     * Executes a statement and returns the values of the first row.
     *
     * This method will prepare and execute the given statement using the
     * `execute()` method. Once executed, the values of the first row is
     * returned.
     *
     * ```php
     * $row = $database->row('SELECT * FROM example WHERE id = :id');
     * ```
     *
     * @param string $statement  The statement to prepare.
     * @param array  $parameters The parameters for the statement.
     *
     * @return array|null The row values, if any.
     */
    public function row($statement, array $parameters = [])
    {
        $executed = $this->execute($statement, $parameters);
        $row = $executed->fetch();

        $this->release($executed);

        return (false === $row) ? null : $row;
    }

    /**
     * Rolls back the current transaction.
     *
     * ```php
     * $database->rollBack();
     * ```
     *
     * @throws CouldNotRollBackTransactionException If the transaction could not be rolled back.
     */
    public function rollBack()
    {
        try {
            $this->release($this->execute('ROLLBACK'));
        } catch (CouldNotExecuteException $exception) {
            throw new CouldNotRollBackTransactionException(
                'The transaction could not be rolled back.',
                $exception
            );
        }
    }

    /**
     * Invokes a callable inside of a transaction.
     *
     * This method will begin a new transaction, invoke the given callable,
     * and then commit the transaction. If the callable throws an exception,
     * the transaction is rolled back and the exception is re-thrown.
     *
     * ```php
     * $database->transactional(
     *     function (Database $database) {
     *         // Execute statements.
     *     }
     * );
     * ```
     *
     * @param callable $callable The callable to invoke.
     *
     * @throws Exception If an exception is thrown.
     */
    public function transactional(callable $callable)
    {
        $this->begin();

        try {
            $callable($this);
        } catch (Exception $exception) {
            $this->rollBack();

            throw $exception;
        }

        $this->commit();
    }

    /**
     * Checks if a statement changes the schema and clears itself.
     *
     * This method will check if the statement will cause a change to the
     * schema. If the schema will be changed then all of the prepared
     * statements are cleared.
     *
     * @param string $statement The statement to check.
     */
    private function clear($statement)
    {
        $statement = strtoupper($statement);

        foreach (self::$changes as $keyword) {
            if (false !== strpos($statement, $keyword)) {
                $this->prepared = [];
                $this->preparedInUse = new SplObjectStorage();

                break;
            }
        }
    }
}
