<?php

namespace Test\KHerGe\Excel\Database;

use KHerGe\Excel\Database;
use KHerGe\Excel\Database\CellsTable;
use KHerGe\Excel\Database\NumberFormatsTable;
use KHerGe\Excel\Database\SharedStringsTable;
use KHerGe\Excel\Database\StylesTable;
use KHerGe\Excel\Database\WorksheetsTable;
use KHerGe\Excel\Reader\SharedStringsReader;
use KHerGe\Excel\Reader\StylesReader;
use KHerGe\Excel\Reader\WorkbookReader;
use KHerGe\Excel\Reader\WorksheetReader;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the cell values table manager function as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\Database\CellsTable
 */
class CellsTableTest extends TestCase
{
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
     * The cell values table manager.
     *
     * @var CellsTable
     */
    private $table;

    /**
     * The worksheets table manager.
     *
     * @var WorksheetsTable
     */
    private $worksheets;

    /**
     * Verify that the number of columns is returned.
     */
    public function testGetTheNumberOfColumnsInAWorksheet()
    {
        $this->insertWorksheet(1, 'Test');

        $this->insertCell(1, 1, 1, 'alpha');
        $this->insertCell(1, 2, 1, 'beta');
        $this->insertCell(1, 3, 1, 'gamma');

        self::assertEquals(
            3,
            $this->table->countColumns(1),
            'The number of columns was not returned.'
        );
    }

    /**
     * Verify that the number of rows is returned.
     */
    public function testGetTheNumberOfRowsInAWorksheet()
    {
        $this->insertWorksheet(1, 'Test');

        $this->insertCell(1, 1, 1, 'alpha');
        $this->insertCell(1, 1, 2, 'beta');
        $this->insertCell(1, 1, 3, 'gamma');

        self::assertEquals(
            3,
            $this->table->countRows(1),
            'The number of rows was not returned.'
        );
    }

    /**
     * Verify that the data for a specific cell is returned.
     */
    public function testGetTheDataForASpecificCell()
    {
        $this->insertWorksheet(1, 'Test');

        $this->insertCell(1, 1, 1, 'test');

        self::assertEquals(
            [
                'column' => 'A',
                'row' => '1',
                'type' => 's',
                'value' => null,
                'shared' => 'test',
                'format' => null
            ],
            $this->table->getCell(1, 'A', 1),
            'The data for the cell was not returned.'
        );
    }

    /**
     * Verify that the data for a specific row is returned.
     */
    public function testGetTheDataForASpecificRowOfCells()
    {
        $this->insertWorksheet(1, 'Test');

        $this->insertCell(1, 1, 1, 'alpha');
        $this->insertCell(1, 2, 1, 'beta');
        $this->insertCell(1, 3, 1, 'gamma');

        self::assertEquals(
            [
                [
                    'column' => 'A',
                    'row' => '1',
                    'type' => 's',
                    'value' => null,
                    'shared' => 'alpha',
                    'format' => null
                ],

                [
                    'column' => 'B',
                    'row' => '1',
                    'type' => 's',
                    'value' => null,
                    'shared' => 'beta',
                    'format' => null
                ],

                [
                    'column' => 'C',
                    'row' => '1',
                    'type' => 's',
                    'value' => null,
                    'shared' => 'gamma',
                    'format' => null
                ]
            ],
            $this->table->getRow(1, 1),
            'The row of cell values was not returned.'
        );
    }

    /**
     * Verify that a cell existence check is performed.
     */
    public function testCheckIfACellExistsInAWorksheet()
    {
        self::assertFalse(
            $this->table->hasCell(1, 'A', 1),
            'The cell should not exist.'
        );

        $this->insertWorksheet(1, 'Test');

        $this->insertCell(1, 1, 1, 'test');

        self::assertTrue(
            $this->table->hasCell(1, 'A', 1),
            'The cell should exist.'
        );
    }

    /**
     * Verify that a column existence check is performed.
     */
    public function testCheckIfAColumnExistsInAWorksheet()
    {
        self::assertFalse(
            $this->table->hasColumn(1, 'A'),
            'The column should not exist.'
        );

        $this->insertWorksheet(1, 'Test');

        $this->insertCell(1, 1, 1, 'test');

        self::assertTrue(
            $this->table->hasColumn(1, 'A'),
            'The column should exist.'
        );
    }

    /**
     * Verify that a row existence check is performed.
     */
    public function testCheckIfARowExistsInAWorksheet()
    {
        self::assertFalse(
            $this->table->hasRow(1, 1),
            'The row should not exist.'
        );

        $this->insertWorksheet(1, 'Test');

        $this->insertCell(1, 1, 1, 'test');

        self::assertTrue(
            $this->table->hasRow(1, 1),
            'The row should exist.'
        );
    }

    /**
     * @depends testGetTheDataForASpecificCell
     *
     * Verify that the worksheet cell values are imported.
     */
    public function testImportTheCellValuesOfAWorksheet()
    {
        $styles = new StylesReader(
            sprintf(
                'zip://%s#xl/styles.xml',
                __DIR__ . '/../../../../res/simple.xlsx'
            )
        );

        $this->numberFormats->import($styles);
        $this->styles->import($styles);

        $this->sharedStrings->import(
            new SharedStringsReader(
                sprintf(
                    'zip://%s#xl/sharedStrings.xml',
                    __DIR__ . '/../../../../res/simple.xlsx'
                )
            )
        );

        $this->worksheets->import(
            new WorkbookReader(
                sprintf(
                    'zip://%s#xl/workbook.xml',
                    __DIR__ . '/../../../../res/simple.xlsx'
                )
            )
        );

        $this->table->import(
            new WorksheetReader(
                sprintf(
                    'zip://%s#xl/worksheets/sheet1.xml',
                    __DIR__ . '/../../../../res/simple.xlsx'
                )
            ),
            1
        );

        self::assertEquals(
            [
                'column' => 'A',
                'row' => '1',
                'type' => 's',
                'value' => null,
                'shared' => 'General',
                'format' => null
            ],
            $this->table->getCell(1, 'A', 1),
            'The worksheet cell values were not imported.'
        );
    }

    /**
     * Verify that each column value in a worksheet is iterated.
     */
    public function testIterateThroughEachValueInAColumnFoAWorksheet()
    {
        $this->insertWorksheet(1, 'Test');

        $this->insertCell(1, 1, 1, 'a');
        $this->insertCell(1, 2, 1, 'b');
        $this->insertCell(1, 3, 1, 'c');

        $this->insertCell(1, 1, 2, 'd');
        $this->insertCell(1, 2, 2, 'e');
        $this->insertCell(1, 3, 2, 'f');

        $this->insertCell(1, 1, 3, 'g');
        $this->insertCell(1, 2, 3, 'h');
        $this->insertCell(1, 3, 3, 'i');

        $iterations = ['a', 'd', 'g'];

        foreach ($this->table->iterateColumn(1, 'A') as $i => $cell) {
            self::assertArrayHasKey(
                $i,
                $iterations,
                'This iteration was not expected.'
            );

            self::assertEquals(
                $iterations[$i],
                $cell['shared'],
                'The cell value was not returned.'
            );

            unset($iterations[$i]);
        }

        self::assertEmpty(
            $iterations,
            'Not all column values were iterated through.'
        );
    }

    /**
     * Verify that each row in a worksheet is iterated.
     */
    public function testIterateThroughEachRowInAWorksheet()
    {
        $this->insertWorksheet(1, 'Test');

        $this->insertCell(1, 1, 1, 'a');
        $this->insertCell(1, 2, 1, 'b');
        $this->insertCell(1, 3, 1, 'c');

        $this->insertCell(1, 1, 2, 'd');
        $this->insertCell(1, 2, 2, 'e');
        $this->insertCell(1, 3, 2, 'f');

        $this->insertCell(1, 1, 3, 'g');
        $this->insertCell(1, 2, 3, 'h');
        $this->insertCell(1, 3, 3, 'i');

        $iterations = [
            ['a', 'b', 'c'],
            ['d', 'e', 'f'],
            ['g', 'h', 'i']
        ];

        foreach ($this->table->iterateRows(1) as $i => $row) {
            self::assertArrayHasKey(
                $i,
                $iterations,
                'This iteration was not expected.'
            );

            foreach ($iterations[$i] as $j => $value) {
                self::assertEquals(
                    $value,
                    $row[$j]['shared'],
                    'The cell value was not returned.'
                );
            }

            unset($iterations[$i]);
        }

        self::assertEmpty(
            $iterations,
            'Not all rows were iterated through.'
        );
    }

    /**
     * Creates a new cell values table manager.
     */
    protected function setUp()
    {
        $this->database = Database::create();

        $this->numberFormats = new NumberFormatsTable($this->database);
        $this->sharedStrings = new SharedStringsTable($this->database);
        $this->styles = new StylesTable($this->database);
        $this->worksheets = new WorksheetsTable($this->database);

        $this->table = new CellsTable($this->database);
    }

    /**
     * Inserts a new cell into the table.
     *
     * @param integer $worksheet The index of the worksheet.
     * @param integer $column    The index of the column.
     * @param integer $row       The number of the row.
     * @param string  $value     The value of the cell.
     * @param string  $type      The type of the value.
     */
    private function insertCell($worksheet, $column, $row, $value, $type = 's')
    {
        static $index = -1;

        $shared = ('s' === $type);

        if ($shared) {
            $index++;

            $this->database->release(
                $this->database->execute(
                    <<<SQL
INSERT INTO strings
            ("index", string)
     VALUES (:index, :string)
SQL
,
                    [
                        'index' => $index,
                        'string' => $value
                    ]
                )
            );
        }

        $this->database->release(
            $this->database->execute(
                <<<SQL
INSERT INTO cells
            ( worksheet, "column", "row",  type,  style,  value,  shared)
     VALUES (:worksheet, :column,  :row,  :type, :style, :value, :shared)
SQL
,
                [
                    'worksheet' => $worksheet,
                    'column' => $column,
                    'row' => $row,
                    'type' => $type,
                    'style' => null,
                    'value' => $shared ? null : $value,
                    'shared' => $shared ? $index : null
                ]
            )
        );
    }

    /**
     * Inserts a new worksheet into the table.
     *
     * @param integer $index The index of the worksheet.
     * @param string  $name  The name of the worksheet.
     */
    private function insertWorksheet($index, $name)
    {

        $this->database->release(
            $this->database->execute(
                <<<SQL
INSERT INTO worksheets
            ("index", name)
     VALUES (:index, :name)
SQL
,
                [
                    'index' => $index,
                    'name' => $name
                ]
            )
        );
    }
}
