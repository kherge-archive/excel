<?php

namespace Test\KHerGe\Excel\SQL;

use KHerGe\Excel\SQL\Database;
use PHPUnit_Framework_TestCase as TestCase;
use RuntimeException;

/**
 * Verifies that the workbook database functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @coversDefaultClass \KHerGe\Excel\SQL\Database
 *
 * @covers ::__construct
 * @covers ::__destruct
 * @covers ::all
 * @covers ::begin
 * @covers ::column
 * @covers ::commit
 * @covers ::createSchema
 * @covers ::execute
 * @covers ::fetch
 * @covers ::iterate
 * @covers ::prepare
 * @covers ::rollBack
 * @covers ::row
 */
class DatabaseTest extends TestCase
{
    /**
     * The workbook database.
     *
     * @var Database
     */
    private $database;

    /**
     * Returns the expected values and their types.
     *
     * @return mixed[] The values and their types.
     */
    public function getExpectedValueTypes()
    {
        return [
            ['string'],
            [true],
            [123],
            [1.23]
        ];
    }

    /**
     * Verify that a cell can be added that uses a shared string.
     *
     * @covers ::addCell
     * @covers ::getCell
     */
    public function testAddACellThatUsesASharedString()
    {
        $shared = 'Test string.';

        $this->database->addWorksheet(1, 'Test Worksheet');
        $this->database->addString(2, $shared);
        $this->database->addCell(1, 2, 3, 2);

        self::assertEquals(
            $shared,
            $this->database->getCell(1, 2, 3),
            'The shared string was not returned.'
        );
    }

    /**
     * Verify that a cell can be added that uses an inline value.
     *
     * @covers ::addCell
     * @covers ::getCell
     */
    public function testAddACellThatUsesAnInlineValue()
    {
        $inline = 'Test string.';

        $this->database->addWorksheet(1, 'Test Worksheet');
        $this->database->addCell(1, 2, 3, null, $inline);

        self::assertEquals(
            $inline,
            $this->database->getCell(1, 2, 3),
            'The inline value was not returned.'
        );
    }

    /**
     * Verify that the type of an inline cell value is preserved.
     *
     * @param mixed $value The value expected.
     *
     * @covers ::addCell
     * @covers ::getCell
     *
     * @dataProvider getExpectedValueTypes
     */
    public function testAddingACellPreservesTheTypeOfTheValue($value)
    {
        $this->database->addWorksheet(1, 'Test Worksheet');
        $this->database->addCell(1, 1, 1, null, $value);

        self::assertSame(
            $value,
            $this->database->getCell(1, 1, 1),
            sprintf(
                'The %s value was not returned.',
                gettype($value)
            )
        );
    }

    /**
     * Verify that a shared string can be added.
     *
     * @covers ::addString
     * @covers ::getString
     */
    public function testAddASharedString()
    {
        $index = 123;
        $string = 'A test string.';

        $this->database->addString($index, $string);

        self::assertEquals(
            $string,
            $this->database->getString($index),
            'The shared string was not returned.'
        );
    }

    /**
     * Verify that worksheets can be added.
     *
     * @covers ::addWorksheet
     * @covers ::getWorksheets
     */
    public function testAddAWorksheet()
    {
        $worksheets = [
            [
                'index' => 123,
                'name' => 'Test Worksheet #123'
            ],
            [
                'index' => 456,
                'name' => 'Test Worksheet #456'
            ],
            [
                'index' => 789,
                'name' => 'Test Worksheet #789'
            ]
        ];

        foreach ($worksheets as $worksheet) {
            $this->database->addWorksheet(
                $worksheet['index'],
                $worksheet['name']
            );
        }

        self::assertEquals(
            $worksheets,
            $this->database->getWorksheets(),
            'The worksheets were not returned.'
        );
    }

    /**
     * Verify that all of the column values are iterated through.
     *
     * @covers ::iterateColumn
     */
    public function testIterateThroughAllOfTheValuesForAColumn()
    {
        $this->database->transaction(
            function (Database $database) {
                $database->addString(1, 'beta');
                $database->addWorksheet(1, 'Test');
                $database->addCell(1, 1, 1, null, 'alpha');
                $database->addCell(1, 1, 2, 1);
                $database->addCell(1, 1, 3, null, 'gamma');
            }
        );

        self::assertEquals(
            [
                'alpha',
                'beta',
                'gamma'
            ],
            iterator_to_array($this->database->iterateColumn(1, 1)),
            'The column values were not returned.'
        );
    }

    /**
     * Verify that all of the row values are iterated through.
     *
     * @covers ::iterateRow
     */
    public function testIterateThroughAllOfTheValuesForARow()
    {
        $this->database->transaction(
            function (Database $database) {
                $database->addString(1, 'beta');
                $database->addWorksheet(1, 'Test');
                $database->addCell(1, 1, 1, null, 'alpha');
                $database->addCell(1, 2, 1, 1);
                $database->addCell(1, 3, 1, null, 'gamma');
            }
        );

        self::assertEquals(
            [
                'alpha',
                'beta',
                'gamma'
            ],
            iterator_to_array($this->database->iterateRow(1, 1)),
            'The row values were not returned.'
        );
    }

    /**
     * Verify that the number of columns for a worksheet is retrieved.
     *
     * @covers ::countColumns
     */
    public function testCountTheNumberOfColumnsInAWorksheet()
    {
        $this->database->transaction(
            function (Database $database) {
                $database->addWorksheet(1, 'Test');
                $database->addCell(1, 1, 1, null, 'test');
                $database->addCell(1, 2, 2, null, 'test');
                $database->addCell(1, 3, 3, null, 'test');
            }
        );

        self::assertEquals(
            3,
            $this->database->countColumns(1),
            'The number of columns was not returned.'
        );
    }

    /**
     * Verify that the number of rows for a worksheet is retrieved.
     *
     * @covers ::countRows
     */
    public function testCountTheNumberOfRowsInAWorksheet()
    {
        $this->database->transaction(
            function (Database $database) {
                $database->addWorksheet(1, 'Test');
                $database->addCell(1, 1, 1, null, 'test');
                $database->addCell(1, 2, 2, null, 'test');
                $database->addCell(1, 3, 2, null, 'test');
            }
        );

        self::assertEquals(
            2,
            $this->database->countRows(1),
            'The number of columns was not returned.'
        );
    }

    /**
     * Verify that the index for a worksheet can be retrieved by its name.
     *
     * @covers ::addWorksheet
     * @covers ::getWorksheetIndex
     */
    public function testGetAWorksheetIndexByItsName()
    {
        $index = 123;
        $name = 'Test Worksheet';

        $this->database->addWorksheet($index, $name);

        self::assertEquals(
            $index,
            $this->database->getWorksheetIndex($name),
            'The index of the worksheet was not returned.'
        );
    }

    /**
     * Verify that the name for a worksheet can be retrieved by its index.
     *
     * @covers ::addWorksheet
     * @covers ::getWorksheetName
     */
    public function testGetAWorksheetNameByItsIndex()
    {
        $index = 123;
        $name = 'Test Worksheet';

        $this->database->addWorksheet($index, $name);

        self::assertEquals(
            $name,
            $this->database->getWorksheetName($index),
            'The name of the worksheet was not returned.'
        );
    }

    /**
     * @depends testAddACellThatUsesASharedString
     * @depends testAddASharedString
     * @depends testAddAWorksheet
     *
     * Verify that records can be added in a transaction.
     *
     * @covers ::transaction
     */
    public function testAddRecordsInATransaction()
    {
        $shared = 'The test string.';

        $this->database->transaction(
            function (Database $database) use ($shared) {
                $database->addWorksheet(1, 'Test Worksheet');
                $database->addString(2, $shared);
                $database->addCell(1, 2, 3, 2);
            }
        );

        self::assertEquals(
            $shared,
            $this->database->getCell(1, 2, 3),
            'The records were not added.'
        );
    }

    /**
     * @depends testAddRecordsInATransaction
     *
     * Verify that an exception will roll back a transaction.
     *
     * @covers ::transaction
     */
    public function testAFailedTransactionIsRolledBack()
    {
        try {
            $this->database->transaction(
                function (Database $database) {
                    $database->addWorksheet(1, 'Test Worksheet');
                    $database->addString(2, 'The test string.');
                    $database->addCell(1, 2, 3, 2);

                    throw new RuntimeException('The test exception.');
                }
            );
        } catch (RuntimeException $exception) {
            // Intentionally do nothing.
        } finally {
            self::assertTrue(
                isset($exception),
                'The test exception was not rethrown.'
            );

            self::assertNull(
                $this->database->getCell(1, 2, 3),
                'The transaction was not rolled back.'
            );
        }
    }

    /**
     * Creates a new workbook database.
     */
    protected function setUp()
    {
        $this->database = new Database();
    }
}
