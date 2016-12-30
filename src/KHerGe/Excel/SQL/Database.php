<?php

namespace KHerGe\Excel\SQL;

use Exception;
use Generator;
use KHerGe\Excel\Exception\SQL\Database\CouldNotBeginTransactionException;
use KHerGe\Excel\Exception\SQL\Database\CouldNotCommitTransactionException;
use KHerGe\Excel\Exception\SQL\Database\CouldNotExecuteException;
use KHerGe\Excel\Exception\SQL\Database\CouldNotFetchException;
use KHerGe\Excel\Exception\SQL\Database\CouldNotPrepareException;
use KHerGe\Excel\Exception\SQL\Database\CouldNotRollBackTransactionException;
use PDO;
use PDOException;
use PDOStatement;

use function KHerGe\File\remove;
use function KHerGe\File\temp_file;

/**
 * Manages the database storing the workbook and worksheet data.
 *
 * This class will create a new temporary SQLite database when instantiated.
 * The purpose of the database is to store the workbook metadata, along with
 * the worksheet metadata and cell values. With a new instance, the database
 * is empty. You will be required to populate the database by providing it
 * import objects.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Database
{
    /**
     * The statement to count the number of columns for a worksheet.
     *
     * @var string
     */
    const COUNT_COLUMNS = <<<SQL
SELECT MAX("column") FROM cells WHERE worksheet = :worksheet;
SQL;

    /**
     * The statement to count the number of rows for a worksheet.
     *
     * @var string
     */
    const COUNT_ROWS = <<<SQL
SELECT MAX("row") FROM cells WHERE worksheet = :worksheet;
SQL;

    /**
     * The statement to insert a cell record.
     *
     * @var string
     */
    const INSERT_CELL = <<<SQL
INSERT INTO cells
            ( worksheet, "column",  encoded, "row",  string,  value)
     VALUES (:worksheet, :column,  :encoded, :row,  :string, :value);
SQL;

    /**
     * The statement to insert a metadata record.
     *
     * @var string
     */
    const INSERT_METADATA = <<<SQL
INSERT INTO metadata (name, value) VALUES (:name, :value);
SQL;

    /**
     * The statement to insert a string record.
     *
     * @var string
     */
    const INSERT_STRING = <<<SQL
INSERT INTO strings ("index", string) VALUES (:index, :string);
SQL;

    /**
     * The statement to insert a worksheet record.
     *
     * @var string
     */
    const INSERT_WORKSHEET = <<<SQL
INSERT INTO worksheets ("index", name) VALUES (:index, :name);
SQL;

    /**
     * The database schema.
     *
     * @var string
     */
    const SCHEMA = <<<SQL
-- Create a table for the worksheets.
CREATE TABLE worksheets (
    "index" INTEGER PRIMARY KEY,
    name    TEXT    NOT NULL
);

-- Create a table for the shared strings.
CREATE TABLE strings (
    "index" INTEGER PRIMARY KEY,
    string  TEXT
);

-- Create a table for the individual cells.
CREATE TABLE cells (
    worksheet INTEGER NOT NULL,
    "column"  INTEGER NOT NULL,
    "row"     INTEGER NOT NULL,
    string    INTEGER,
    value     TEXT,
    encoded   INTEGER NOT NULL,
    
    FOREIGN KEY (string)    REFERENCES strings("index"),
    FOREIGN KEY (worksheet) REFERENCES worksheets("index"),

    PRIMARY KEY (worksheet, column, row)
);
SQL;

    /**
     * The statement to select an individual cell record.
     *
     * @var string
     */
    const SELECT_CELL = <<<SQL
   SELECT strings.string AS string,
          cells.value AS value,
          cells.encoded AS encoded
     FROM cells
LEFT JOIN strings ON strings."index" = cells.string
    WHERE "column" = :column
      AND "row" = :row
      AND worksheet = :worksheet
SQL;

    /**
     * The statement to select an entire column for a worksheet.
     *
     * @var string
     */
    const SELECT_COLUMN = <<<SQL
   SELECT strings.string AS string,
          cells.value AS value
     FROM cells
LEFT JOIN strings ON strings."index" = cells.string
    WHERE "column" = :column
      AND worksheet = :worksheet
SQL;

    /**
     * The statement to select an individual metadata record.
     *
     * @var string
     */
    const SELECT_METADATA = <<<SQL
SELECT value FROM metadata WHERE name = :name
SQL;

    /**
     * The statement to select an entire row for a worksheet.
     *
     * @var string
     */
    const SELECT_ROW = <<<SQL
   SELECT strings.string AS string,
          cells.value AS value
     FROM cells
LEFT JOIN strings ON strings."index" = cells.string
    WHERE "row" = :row
      AND worksheet = :worksheet
SQL;

    /**
     * The statement to select an individual string record.
     *
     * @var string
     */
    const SELECT_STRING = <<<SQL
SELECT string FROM strings WHERE "index" = :index
SQL;

    /**
     * The statement to select all of the worksheet records.
     *
     * @var string
     */
    const SELECT_WORKSHEETS = <<<SQL
SELECT * FROM worksheets
SQL;

    /**
     * The statement to select an individual worksheet record by name.
     *
     * @var string
     */
    const SELECT_WORKSHEET_INDEX = <<<SQL
SELECT "index" FROM worksheets WHERE name = :name
SQL;

    /**
     * The statement to select an individual worksheet record by index.
     *
     * @var string
     */
    const SELECT_WORKSHEET_NAME = <<<SQL
SELECT name FROM worksheets WHERE "index" = :index
SQL;

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
    private $statements = [];

    /**
     * Initializes the database by creating a new one.
     */
    public function __construct()
    {
        $this->file = temp_file();

        $this->createSchema();
    }

    /**
     * Deletes the temporary database file.
     */
    public function __destruct()
    {
        $this->pdo = null;

        remove($this->file);
    }

    /**
     * Adds the information for an individual worksheet cell.
     *
     * This method will insert a new record into the database that represents
     * an individual cell in a worksheet. The `$worksheet`, `$column`, and
     * `$row` are expected to be a unique tuple. You are also expected to
     * provide either `$string` or `$value`, depending on the contents of
     * the cell and whether or not it uses a shared string.
     *
     * ```php
     * // Using a shared string.
     * $database->addCell(1, 'C', 3, 123);
     *
     * // Using a inline value.
     * $database->addCell(1, 'C', 3, null, 'example');
     * ```
     *
     * @param integer      $worksheet The index of the worksheet.
     * @param integer      $column    The index of the column.
     * @param integer      $row       The index of the row.
     * @param integer|null $string    The index of the shared string.
     * @param null|mixed   $value     The inline value of the cell.
     */
    public function addCell(
        $worksheet,
        $column,
        $row,
        $string = null,
        $value = null
    ) {
        $encoded = 0;

        if ((null !== $value) && !is_string($value)) {
            $encoded = 1;
            $value = json_encode($value);
        }

        $this->execute(
            self::INSERT_CELL,
            [
                'column' => $column,
                'encoded' => $encoded,
                'row' => $row,
                'string' => $string,
                'value' => $value,
                'worksheet' => $worksheet
            ]
        );
    }

    /**
     * Adds a shared string.
     *
     * ```php
     * $database->addString(123, 'example');
     * ```
     *
     * @param integer $index  The index of the shared string.
     * @param string  $string The shared string.
     */
    public function addString($index, $string)
    {
        $this->execute(
            self::INSERT_STRING,
            [
                'index' => $index,
                'string' => $string
            ]
        );
    }

    /**
     * Adds the information for a worksheet.
     *
     * ```php
     * $database->addWorksheet(123, 'Example');
     * ```
     *
     * @param integer $index The index of the worksheet.
     * @param string  $name  The name of the worksheet.
     */
    public function addWorksheet($index, $name)
    {
        $this->execute(
            self::INSERT_WORKSHEET,
            [
                'index' => $index,
                'name' => $name
            ]
        );
    }

    /**
     * Counts the number of columns for a worksheet.
     *
     * ```php
     * $count = $database->countColumns(1);
     * ```
     *
     * @param integer $worksheet The index of the worksheet.
     *
     * @return integer The number of columns.
     */
    public function countColumns($worksheet)
    {
        return $this->column(
            self::COUNT_COLUMNS,
            ['worksheet' => $worksheet]
        );
    }

    /**
     * Counts the number of rows for a worksheet.
     *
     * ```php
     * $count = $database->countRows(1);
     * ```
     *
     * @param integer $worksheet The index of the worksheet.
     *
     * @return integer The number of rows.
     */
    public function countRows($worksheet)
    {
        return $this->column(
            self::COUNT_ROWS,
            ['worksheet' => $worksheet]
        );
    }

    /**
     * Returns the cell value.
     *
     * This method will retrieve the information for the cell and resolve the
     * actual value of the cell. If the cell value is a shared string, the
     * shared string itself is returned. Otherwise, the inline value of the
     * cell is returned.
     *
     * ```php
     * $value = $database->getCell(1, 'C', 3);
     * ```
     *
     * @param integer $worksheet The index of the worksheet.
     * @param integer $column    The index of the column.
     * @param integer $row       The index of the row.
     *
     * @return string The value of the cell.
     */
    public function getCell($worksheet, $column, $row)
    {
        $cell = $this->row(
            self::SELECT_CELL,
            [
                'column' => $column,
                'row' => $row,
                'worksheet' => $worksheet
            ]
        );

        if (null === $cell['string']) {
            if (1 == $cell['encoded']) {
                $value = json_decode($cell['value']);
            } else {
                $value = $cell['value'];
            }
        } else {
            $value = $cell['string'];
        }

        return $value;
    }

    /**
     * Returns a shared string.
     *
     * ```php
     * $string = $database->getString(123);
     * ```
     *
     * @param integer $index The index of the shared string.
     *
     * @return null|string The shared string or `null` if none.
     */
    public function getString($index)
    {
        return $this->column(self::SELECT_STRING, ['index' => $index]);
    }

    /**
     * Returns all of the worksheets in the database.
     *
     * The array return contains one array for each worksheet in the database.
     * Each of those arrays contains the `name` and `index` of the worksheet.
     *
     * ```php
     * $worksheets = $database->getWorksheets();
     * ```
     *
     * @return array[] The information for the worksheets.
     */
    public function getWorksheets()
    {
        return $this->all(self::SELECT_WORKSHEETS);
    }

    /**
     * Finds a worksheet by its name and returns its index.
     *
     * ```php
     * $index = $database->getWorksheetIndex('Example');
     * ```
     *
     * @param string $name The name of the worksheet.
     *
     * @return integer|null The index of the worksheet or `null` if none.
     */
    public function getWorksheetIndex($name)
    {
        return $this->column(self::SELECT_WORKSHEET_INDEX, ['name' => $name]);
    }

    /**
     * Finds a worksheet by its index and returns its name.
     *
     * ```php
     * $name = $database->getWorksheetName(123);
     * ```
     *
     * @param integer $index The index of the worksheet.
     *
     * @return null|string The name of the worksheet or `null` if none.
     */
    public function getWorksheetName($index)
    {
        return $this->column(self::SELECT_WORKSHEET_NAME, ['index' => $index]);
    }

    /**
     * Iterates through each value in a column for a worksheet.
     *
     * ```php
     * foreach ($database->iterateColumn(123) as $value) {
     *     // ...
     * }
     * ```
     *
     * @param integer $worksheet The index of the worksheet.
     * @param integer $column    The index of the column.
     *
     * @return Generator|string[] The values in the column.
     */
    public function iterateColumn($worksheet, $column)
    {
        $generator = $this->iterate(
            self::SELECT_COLUMN,
            [
                'column' => $column,
                'worksheet' => $worksheet
            ]
        );

        foreach ($generator as $cell) {
            yield (null === $cell['string'])
                ? $cell['value']
                : $cell['string'];
        }
    }

    /**
     * Iterates through each value in a row for a worksheet.
     *
     * ```php
     * foreach ($database->iterateRow(123) as $value) {
     *     // ...
     * }
     * ```
     *
     * @param integer $worksheet The index of the worksheet.
     * @param integer $row       The row number.
     *
     * @return Generator|string[] The values in the row.
     */
    public function iterateRow($worksheet, $row)
    {
        $generator = $this->iterate(
            self::SELECT_ROW,
            [
                'row' => $row,
                'worksheet' => $worksheet
            ]
        );

        foreach ($generator as $cell) {
            yield (null === $cell['string'])
                ? $cell['value']
                : $cell['string'];
        }
    }

    /**
     * Executes a function inside a transaction.
     *
     * This method will begin a new transaction, invoke the given callable,
     * and then commit the transaction. If an exception is thrown during the
     * invocation, the transaction will be rolled back before the exception
     * is rethrown.
     *
     * ```php
     * $database->transaction(
     *     function (Database $database) {
     *         // ...
     *     }
     * );
     * ```
     *
     * @param callable $callable The callable to invoke.
     *
     * @throws Exception If an exception is thrown.
     */
    public function transaction(callable $callable)
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
     * Returns all of the rows from an executed statement.
     *
     * ```php
     * $rows = $this->all($statement, $parameters);
     * ```
     *
     * @param string $statement  The SQL statement.
     * @param array  $parameters The parameter values.
     *
     * @return array[] The rows.
     *
     * @throws CouldNotFetchException If the rows could not be fetched.
     */
    private function all($statement, array $parameters = [])
    {
        $executed = $this->execute($statement, $parameters);

        try {
            $rows = $executed->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            throw new CouldNotFetchException(
                'The rows could not be fetched from the statement.',
                $executed
            );
        }

        $executed->closeCursor();

        return $rows;
    }

    /**
     * Begins a new transaction.
     *
     * ```php
     * $this->begin();
     * ```
     *
     * @throws CouldNotBeginTransactionException If the transaction could not be started.
     */
    private function begin()
    {
        try {
            $this->pdo->beginTransaction();
        } catch (PDOException $exception) {
            throw new CouldNotBeginTransactionException(
                'The transaction could not be started.',
                $exception
            );
        }
    }

    /**
     * Returns the value of the first column from an executed statement.
     *
     * ```php
     * $value = $this->column($statement, $parameters);
     * ```
     *
     * @param string $statement  The SQL statement.
     * @param array  $parameters The parameter values.
     *
     * @return null|string The value of the column or `null` if none.
     */
    private function column($statement, array $parameters = [])
    {
        $executed = $this->execute($statement, $parameters);
        $column = $this->fetch($executed, PDO::FETCH_COLUMN);

        $executed->closeCursor();

        return (false === $column) ? null : $column;
    }

    /**
     * Commits the current transaction.
     *
     * ```php
     * $this->commit();
     * ```
     *
     * @throws CouldNotCommitTransactionException If the transaction could not be committed.
     */
    private function commit()
    {
        try {
            $this->pdo->commit();
        } catch (PDOException $exception) {
            throw new CouldNotCommitTransactionException(
                'The transaction could not be committed.',
                $exception
            );
        }
    }

    /**
     * Creates the schema for the new database.
     *
     * This method will create a new PDO instance and enable exceptions for
     * error handling. Once the database schema is created, the database file
     * will be created by the SQLite database driver. To clean up, the PDO
     * connection should be closed before deleting the database file.
     */
    private function createSchema()
    {
        // Create the new SQLite database file.
        $this->pdo = new PDO('sqlite:' . $this->file);

        // Throw exceptions for all errors.
        $this->pdo->setAttribute(
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_EXCEPTION
        );

        // Create the schema.
        $this->pdo->exec(self::SCHEMA);

        // Enable foreign key checks.
        $this->pdo->exec('PRAGMA foreign_keys = 1');
    }

    /**
     * Prepares and then executes a SQL statement.
     *
     * This method will prepare the given statement using `prepare()` and then
     * execute it with the given parameters. The executed statement will then
     * be returned.
     *
     * ```php
     * $executed = $this->execute($statement, $parameters);
     * ```
     *
     * @param string $statement  The SQL statement.
     * @param array  $parameters The parameter values.
     *
     * @return PDOStatement The executed statement.
     *
     * @throws CouldNotExecuteException If the statement could not be executed.
     */
    private function execute($statement, array $parameters = [])
    {
        $prepared = $this->prepare($statement);

        try {
            $prepared->execute($parameters);
        } catch (PDOException $exception) {
            throw new CouldNotExecuteException(
                'The prepared statement "%s" could not be executed using: %s',
                $statement,
                json_encode($parameters),
                $exception
            );
        }

        return $prepared;
    }

    /**
     * Fetches a row from the statement's results.
     *
     * ```php
     * $row = $this->fetch($statement);
     * ```
     *
     * @param PDOStatement $statement The executed statement.
     * @param integer      $mode      The fetching mode.
     *
     * @return array|boolean The row or `false` if there is no row.
     *
     * @throws CouldNotFetchException If the row could not be fetched.
     */
    private function fetch(PDOStatement $statement, $mode = PDO::FETCH_ASSOC)
    {
        try {
            return $statement->fetch($mode);
        } catch (PDOException $exception) {
            throw new CouldNotFetchException(
                'A row could not be fetched from the statement.'
            );
        }
    }

    /**
     * Prepares and executes a statement, yielding each row in the result.
     *
     * This method will prepare the given statement, execute it, and loop
     * through all of the rows in the result. Each row will be yielded before
     * the next row is fetched. If not all rows are iterated through, the
     * results are freed.
     *
     * ```php
     * foreach ($this->iterate($statement, $parameters) as $row) {
     *     // ...
     * }
     * ```
     *
     * @param string $statement  The SQL statement.
     * @param array  $parameters The parameter values.
     *
     * @return Generator|array[] The resulting rows.
     */
    private function iterate($statement, array $parameters = [])
    {
        $executed = $this->execute($statement, $parameters);
        $executed->setFetchMode(PDO::FETCH_ASSOC);

        try {
            foreach ($executed as $row) {
                yield $row;
            }
        } finally {
            $executed->closeCursor();
        }
    }

    /**
     * Prepares a SQL statement for execution.
     *
     * This method will prepare the given statement only once. Each subsequent
     * call to this method for the same statement will return the previously
     * prepared statement.
     *
     * ```php
     * $statement = $this->prepare('SELECT * FROM example');
     * ```
     *
     * @param string $statement The SQL statement.
     *
     * @return PDOStatement The prepared statement.
     *
     * @throws CouldNotPrepareException If the statement could not be prepared.
     */
    private function prepare($statement)
    {
        if (!isset($this->statements[$statement])) {
            try {
                $this->statements[$statement] = $this->pdo->prepare($statement);
            } catch (PDOException $exception) {
                throw new CouldNotPrepareException(
                    'The SQL statement could not be prepared: %s',
                    $statement,
                    $exception
                );
            }
        }

        return $this->statements[$statement];
    }

    /**
     * Rolls back the current transaction.
     *
     * ```php
     * $this->rollBack();
     * ```
     *
     * @throws CouldNotRollBackTransactionException If the transaction could not be rolled back.
     */
    private function rollBack()
    {
        try {
            $this->pdo->rollBack();
        } catch (PDOException $exception) {
            throw new CouldNotBeginTransactionException(
                'The transaction could not be rolled back.',
                $exception
            );
        }
    }

    /**
     * Returns only the first row from an executed statement.
     *
     * ```php
     * $row = $this->row($statement, $parameters);
     * ```
     *
     * @param string $statement  The SQL statement.
     * @param array  $parameters The parameter values.
     *
     * @return array|null The first row or `null` if there is none.
     *
     */
    private function row($statement, array $parameters = [])
    {
        $executed = $this->execute($statement, $parameters);
        $row = $this->fetch($executed);

        $executed->closeCursor();

        return $row ?: null;
    }
}
