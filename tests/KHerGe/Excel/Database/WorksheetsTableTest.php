<?php

namespace Test\KHerGe\Excel\Database;

use KHerGe\Excel\Database;
use KHerGe\Excel\Database\WorksheetsTable;
use KHerGe\Excel\Reader\WorkbookReader;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the worksheets table manager functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\Database\WorksheetsTable
 */
class WorksheetsTableTest extends TestCase
{
    /**
     * The database manager.
     *
     * @var Database
     */
    private $database;

    /**
     * The worksheets table manager.
     *
     * @var WorksheetsTable
     */
    private $table;

    /**
     * Verify that the table is created.
     */
    public function testTheWorksheetsTableIsCreated()
    {
        $count = 'SELECT COUNT(*) FROM sqlite_master WHERE name = "worksheets"';

        self::assertEquals(
            '1',
            $this
                ->database
                ->execute($count)
                ->fetchColumn(),
            'The worksheets table was not created.'
        );
    }

    /**
     * Verify that the number of worksheets is returned.
     */
    public function testCountTheNumberOfWorksheetsInTheTable()
    {
        $this->insertWorksheet(1, 'First');
        $this->insertWorksheet(2, 'Second');
        $this->insertWorksheet(3, 'Third');

        self::assertSame(
            3,
            count($this->table),
            'The number of worksheets was not returned.'
        );
    }

    /**
     * Verify that the index is returned.
     */
    public function testGetTheIndexByAName()
    {
        $this->insertWorksheet(123, 'Test');

        self::assertSame(
            123,
            $this->table->getIndexByName('Test'),
            'The index of the worksheet was not returned.'
        );
    }

    /**
     * Verify that the list of worksheets is returned.
     */
    public function testGetTheListOfWorksheets()
    {
        $this->insertWorksheet(1, 'First');
        $this->insertWorksheet(2, 'Second');
        $this->insertWorksheet(3, 'Third');

        self::assertEquals(
            [
                1 => 'First',
                2 => 'Second',
                3 => 'Third'
            ],
            $this->table->getList(),
            'The list of worksheets was not returned.'
        );
    }

    /**
     * Verify that the name is returned.
     */
    public function testGetTheNameByAnIndex()
    {
        $this->insertWorksheet(123, 'Test');

        self::assertSame(
            'Test',
            $this->table->getNameByIndex(123),
            'The name of the worksheet was not returned.'
        );
    }

    /**
     * Verify that an existence check is performed by index.
     */
    public function testCheckIfAWorksheetExistsByIndex()
    {
        self::assertFalse(
            $this->table->hasIndex(123),
            'The worksheet should not exist.'
        );

        $this->insertWorksheet(123, 'Test');

        self::assertTrue(
            $this->table->hasIndex(123),
            'The worksheet should exist.'
        );
    }

    /**
     * Verify that an existence check is performed by name.
     */
    public function testCheckIfAWorksheetExistsByName()
    {
        self::assertFalse(
            $this->table->hasName('Test'),
            'The worksheet should not exist.'
        );

        $this->insertWorksheet(123, 'Test');

        self::assertTrue(
            $this->table->hasName('Test'),
            'The worksheet should exist.'
        );
    }

    /**
     * @depends testGetTheListOfWorksheets
     *
     * Verify that the workbook worksheet list is imported.
     */
    public function testImportWorkbookWorksheetList()
    {
        $reader = new WorkbookReader(
            sprintf(
                'zip://%s#xl/workbook.xml',
                __DIR__ . '/../../../../res/complex.xlsx'
            )
        );

        $this->table->import($reader);

        self::assertEquals(
            [
                1 => 'First',
                2 => 'Second',
                3 => 'Third'
            ],
            $this->table->getList(),
            'The worksheet lists was not imported.'
        );
    }

    /**
     * Creates a new worksheets table manager.
     */
    protected function setUp()
    {
        $this->database = Database::create();
        $this->table = new WorksheetsTable($this->database);
    }

    /**
     * Inserts a worksheet.
     */
    private function insertWorksheet($index, $name)
    {
        $this->database->release(
            $this->database->execute(
                WorksheetsTable::INSERT,
                [
                    'index' => $index,
                    'name' => $name
                ]
            )
        );
    }
}
