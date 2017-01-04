<?php

namespace Test\KHerGe\Excel;

use DateTime;
use DateInterval;
use KHerGe\Excel\Database;
use KHerGe\Excel\Database\CellsTable;
use KHerGe\Excel\Database\NumberFormatsTable;
use KHerGe\Excel\Database\SharedStringsTable;
use KHerGe\Excel\Database\StylesTable;
use KHerGe\Excel\Database\WorksheetsTable;
use KHerGe\Excel\Exception\Worksheet\NoSuchCellException;
use KHerGe\Excel\Exception\Worksheet\NoSuchRowException;
use KHerGe\Excel\Reader\SharedStringsReader;
use KHerGe\Excel\Reader\StylesReader;
use KHerGe\Excel\Reader\WorkbookReader;
use KHerGe\Excel\Reader\WorksheetReader;
use KHerGe\Excel\Worksheet;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the worksheet manager functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\Worksheet
 */
class WorksheetTest extends TestCase
{
    /**
     * The cell values table manager.
     *
     * @var CellsTable
     */
    private $cells;

    /**
     * The database manager.
     *
     * @var Database
     */
    private $database;

    /**
     * The number formats table manager.
     *
     * @var NumberFormatsTable
     */
    private $numberFormats;

    /**
     * The shared strings table manager.
     *
     * @var SharedStringsTable
     */
    private $sharedStrings;

    /**
     * The styles table manager.
     *
     * @var StylesTable
     */
    private $styles;

    /**
     * The worksheet manager.
     *
     * @var Worksheet
     */
    private $worksheet;

    /**
     * The worksheets table manager.
     *
     * @var WorksheetsTable
     */
    private $worksheets;

    /**
     * Verify that the number of columns is returned.
     */
    public function testGetTheNumberOfColumns()
    {
        self::assertEquals(
            9,
            $this->worksheet->countColumns(),
            'The number of columns was not returned.'
        );
    }

    /**
     * Verify that the number of rows is returned.
     */
    public function testGetTheNumberOfRows()
    {
        self::assertEquals(
            4,
            $this->worksheet->countRows(),
            'The number of rows was not returned.'
        );
    }

    /**
     * Verify that the value of a specific cell is returned.
     */
    public function testGetTheValueOfASpecificCell()
    {
        self::assertEquals(
            'General',
            $this->worksheet->getCell('A', 1),
            'The value of the cell was not returned.'
        );
    }

    /**
     * @depends testGetTheValueOfASpecificCell
     *
     * Verify that an exception is thrown if the cell does not exist.
     */
    public function testThrowAnExceptionIfTheCellDoesNotExist()
    {
        $this->expectException(NoSuchCellException::class);
        $this->expectExceptionMessage(
            'The cell for column "Z" row "999" in the worksheet "First" (index: 1) does not exist.'
        );

        $this->worksheet->getCell('Z', 999);
    }

    /**
     * Verify that the index of the worksheet is returned.
     */
    public function testGetTheIndexOfTheWorksheet()
    {
        self::assertEquals(
            1,
            $this->worksheet->getIndex(),
            'The index of the worksheet was not returned.'
        );
    }

    /**
     * Verify that the name of the worksheet is returned.
     */
    public function testGetTheNameOfTheWorksheet()
    {
        self::assertEquals(
            'First',
            $this->worksheet->getName(),
            'The name of the worksheet was not returned.'
        );
    }

    /**
     * Verify that a row of cell values is returned.
     */
    public function testGetARowOfCellValues()
    {
        self::assertEquals(
            [
                'A' => 'General',
                'B' => 'Number',
                'C' => 'DateTime',
                'D' => 'Date',
                'E' => 'Time',
                'F' => 'Shared String',
                'G' => 'Rich String',
                'H' => 'Money',
                'I' => 'Fraction'
            ],
            $this->worksheet->getRow(1),
            'The row of cell values was not returned.'
        );
    }

    /**
     * @depends testGetARowOfCellValues
     *
     * Verify that an exception is thrown when the row does not exist.
     */
    public function testThrowAnExceptionWhenTheRowDoesNotExist()
    {
        $this->expectException(NoSuchRowException::class);
        $this->expectExceptionMessage(
            'The row "999" does not exist in the worksheet "First" (index: 1).'
        );

        $this->worksheet->getRow(999);
    }

    /**
     * Verify that a cell is checked for existence.
     */
    public function testCheckIfASpecificCellExists()
    {
        self::assertTrue(
            $this->worksheet->hasCell('A', 1),
            'The cell should exist.'
        );
    }

    /**
     * Verify that a column is checked for existence.
     */
    public function testCheckIfAColumnExists()
    {
        self::assertTrue(
            $this->worksheet->hasColumn('A'),
            'The column should exist.'
        );
    }

    /**
     * Verify that a row is checked for existence.
     */
    public function testCheckIfARowExists()
    {
        self::assertTrue(
            $this->worksheet->hasRow(1),
            'The row should exist.'
        );
    }

    /**
     * Verify that a column is iterated.
     */
    public function testIterateThroughTheValuesInAColumn()
    {
        $iterations = [
            1 => 'Number',
            2 => 1,
            3 => null
        ];

        foreach ($this->worksheet->iterateColumn('B') as $i => $value) {
            self::assertArrayHasKey(
                $i,
                $iterations,
                'This iteration was not expected.'
            );

            self::assertEquals(
                $iterations[$i],
                $value,
                'The expected cell value was not returned.'
            );

            unset($iterations[$i]);
        }

        self::assertEmpty(
            $iterations,
            'Not all of the values in the column were iterated.'
        );
    }

    /**
     * Verify that all rows are iterated.
     */
    public function testIterateThroughAllOfTheRows()
    {
        $iterations = [
            1 => [
                'A' => 'General',
                'B' => 'Number',
                'C' => 'DateTime',
                'D' => 'Date',
                'E' => 'Time',
                'F' => 'Shared String',
                'G' => 'Rich String',
                'H' => 'Money',
                'I' => 'Fraction'
            ],

            2 => [
                'A' => 'General',
                'B' => 1,
                'C' => new DateTime('2016-12-28T13:59:00'),
                'D' => new DateTime('2016-12-28'),
                'E' => new DateInterval('PT13H59M'),
                'F' => 'This is a shared string.',
                'G' => 'This is a rich text string.',
                'H' => 1.39,
                'I' => 0.5
            ],

            3 => [
                'A' => 'Column Span',
                'B' => null,
                'C' => 'Row Span'
            ],

            4 => [
                'C' => null
            ]
        ];

        foreach ($this->worksheet->iterateRows() as $i => $row) {
            self::assertArrayHasKey(
                $i,
                $iterations,
                'This iteration was not expected.'
            );

            self::assertEquals(
                $iterations[$i],
                $row,
                'The expected row values were not returned.'
            );

            unset($iterations[$i]);
        }

        self::assertEmpty(
            $iterations,
            'Not all of the rows were iterated.'
        );
    }

    /**
     * Creates a new worksheet manager.
     */
    protected function setUp()
    {
        $this->database = Database::create();

        $this->numberFormats = new NumberFormatsTable($this->database);
        $this->sharedStrings = new SharedStringsTable($this->database);
        $this->styles = new StylesTable($this->database);
        $this->worksheets = new WorksheetsTable($this->database);
        $this->cells = new CellsTable($this->database);

        $styles = new StylesReader(
            sprintf(
                'zip://%s#xl/styles.xml',
                __DIR__ . '/../../../res/simple.xlsx'
            )
        );

        $this->numberFormats->import($styles);
        $this->styles->import($styles);

        $this->sharedStrings->import(
            new SharedStringsReader(
                sprintf(
                    'zip://%s#xl/sharedStrings.xml',
                    __DIR__ . '/../../../res/simple.xlsx'
                )
            )
        );

        $this->worksheets->import(
            new WorkbookReader(
                sprintf(
                    'zip://%s#xl/workbook.xml',
                    __DIR__ . '/../../../res/simple.xlsx'
                )
            )
        );

        $this->cells->import(
            new WorksheetReader(
                sprintf(
                    'zip://%s#xl/worksheets/sheet1.xml',
                    __DIR__ . '/../../../res/simple.xlsx'
                )
            ),
            1
        );

        $this->worksheet = new Worksheet(
            $this->cells,
            1,
            'First'
        );
    }
}
