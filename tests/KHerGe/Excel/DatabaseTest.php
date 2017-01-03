<?php

namespace Test\KHerGe\Excel;

use KHerGe\Excel\Database;
use KHerGe\Excel\Exception\Database\CouldNotExecuteException;
use KHerGe\Excel\Exception\Database\CouldNotPrepareException;
use KHerGe\Excel\Exception\Database\PreparedStatementInUseException;
use PDO;
use PDOStatement;
use PHPUnit_Framework_TestCase as TestCase;
use RuntimeException;

use function KHerGe\File\temp_file;

/**
 * Verifies that the database manager functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\Database
 */
class DatabaseTest extends TestCase
{
    /**
     * The database manager.
     *
     * @var Database
     */
    private $database;

    /**
     * The temporary file.
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
     * Verify that a new database manager is returned.
     */
    public function testCreateANewDatabaseManager()
    {
        self::assertInstanceOf(
            Database::class,
            Database::create(),
            'The new database manager was not returned.'
        );
    }

    /**
     * Verify that a prepared statement is returned.
     */
    public function testPrepareASqlStatement()
    {
        self::assertInstanceOf(
            PDOStatement::class,
            $this->database->prepare(
                'CREATE TABLE example (id INTEGER PRIMARY KEY);'
            ),
            'The prepared statement was not returned.'
        );
    }

    /**
     * Verify that a failed prepared statement throws an exception.
     */
    public function testThrowAnExceptionWhenAStatementIsNotPrepared()
    {
        $this->expectException(CouldNotPrepareException::class);

        $this->database->prepare('SELECT * FROM example');
    }

    /**
     * @depends testPrepareASqlStatement
     *
     * Verify that a prepared statement cannot be used twice concurrently.
     */
    public function testThrowAnExceptionIfUsingAPreparedStatementTwiceConcurrently()
    {
        $statement = 'CREATE TABLE example (id INTEGER PRIMARY KEY);';

        $this->database->prepare($statement);

        $this->expectException(PreparedStatementInUseException::class);

        $this->database->prepare($statement);
    }

    /**
     * @depends testThrowAnExceptionIfUsingAPreparedStatementTwiceConcurrently
     *
     * Verify that a prepared statement is released for re-use.
     */
    public function testReleaseAPreparedStatement()
    {
        $statement = 'CREATE TABLE example (id INTEGER PRIMARY KEY);';

        $prepared = $this->database->prepare($statement);

        $this->database->release($prepared);
        $this->database->prepare($statement);
    }

    /**
     * @depends testPrepareASqlStatement
     *
     * Verify that an executed statement is returned.
     */
    public function testExecuteASqlStatement()
    {
        self::assertInstanceOf(
            PDOStatement::class,
            $this->database->execute(
                'CREATE TABLE example (id INTEGER PRIMARY KEY);'
            ),
            'The executed statement was not returned.'
        );

        $this->assertTableExists('example');
    }

    /**
     * @depends testExecuteASqlStatement
     *
     * Verify that a failed executed statement throws an exception.
     */
    public function testThrowAnExceptionWhenAnExecutedStatementFails()
    {
        $this->database->release(
            $this->database->execute(
                'CREATE TABLE example (id INTEGER PRIMARY KEY);'
            )
        );

        $this->expectException(CouldNotExecuteException::class);

        $this->database->execute(
            'CREATE TABLE example (id INTEGER PRIMARY KEY);'
        );
    }

    /**
     * @depends testExecuteASqlStatement
     *
     * Verify that a transaction operation is invoked.
     */
    public function testInvokeATransactionalOperation()
    {
        $this->database->transactional(
            function (Database $database) {
                $database->release(
                    $database->execute(
                        'CREATE TABLE example (id INTEGER PRIMARY KEY);'
                    )
                );
            }
        );

        $this->assertTableExists('example');
    }

    /**
     * @depends testInvokeATransactionalOperation
     *
     * Verify that a transaction operation is rolled back.
     */
    public function testRollBackATransactionalOperation()
    {
        try {
            $this->database->transactional(
                function (Database $database) {
                    $database->release(
                        $database->execute(
                            'CREATE TABLE example (id INTEGER PRIMARY KEY);'
                        )
                    );

                    throw new RuntimeException('Force a roll back.');
                }
            );
        } catch (RuntimeException $exception) {
        }

        self::assertTrue(
            isset($exception),
            'The exception was not re-thrown.'
        );

        $this->assertTableNotExists('example');
    }

    /**
     * Creates a new database manager.
     */
    protected function setUp()
    {
        $this->file = temp_file();
        $this->pdo = new PDO('sqlite:' . $this->file);

        $this->database = new Database($this->file, $this->pdo);
    }

    /**
     * Deletes the temporary database file.
     */
    protected function tearDown()
    {
        $this->database = null;

        self::assertFileNotExists(
            $this->file,
            'The database file was not deleted.'
        );
    }

    /**
     * Asserts that a table does not exist.
     *
     * @param string $name The name of the table.
     */
    private function assertTableNotExists($name)
    {
        self::assertEquals(
            '0',
            $this
                ->database
                ->execute(
                    <<<SQL
SELECT COUNT(*)
  FROM sqlite_master
 WHERE type = 'table'
   AND name = :name
;
SQL
,
                    [
                        'name' => $name
                    ]
                )
                ->fetchColumn(),
            "The table \"$name\" exists."
        );
    }

    /**
     * Asserts that a table exists.
     *
     * @param string $name The name of the table.
     */
    private function assertTableExists($name)
    {
        self::assertEquals(
            '1',
            $this
                ->database
                ->execute(
                    <<<SQL
SELECT COUNT(*)
  FROM sqlite_master
 WHERE type = 'table'
   AND name = :name
;
SQL
,
                    [
                        'name' => $name
                    ]
                )
                ->fetchColumn(),
            "The table \"$name\" does not exist."
        );
    }
}
