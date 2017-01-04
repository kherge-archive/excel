<?php

namespace Test\KHerGe\Excel\Database;

use KHerGe\Excel\Database;
use KHerGe\Excel\Database\NumberFormatsTable;
use KHerGe\Excel\Database\StylesTable;
use KHerGe\Excel\Reader\StylesReader;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the cell styles table manager functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\Database\StylesTable
 */
class StylesTableTest extends TestCase
{
    /**
     * The database manager.
     *
     * @var Database
     */
    private $database;

    /**
     * The cell styles table manager.
     *
     * @var StylesTable
     */
    private $table;

    /**
     * Verify that the cell styles are imported.
     */
    public function testImportTheStyles()
    {
        $reader = new StylesReader(
            sprintf(
                'zip://%s#xl/styles.xml',
                __DIR__ . '/../../../../res/simple.xlsx'
            )
        );

        $this->table->import($reader);

        $select = 'SELECT * FROM styles WHERE id = 6;';

        self::assertEquals(
            [
                'id' => 6,
                'isNumber' => 1,
                'numberFormat' => 8
            ],
            $this
                ->database
                ->row($select),
            'The cell styles were not imported.'
        );
    }

    /**
     * Creates a new cell styles table manager.
     */
    protected function setUp()
    {
        $this->database = Database::create();

        (new NumberFormatsTable($this->database))->import(
            new StylesReader(
                sprintf(
                    'zip://%s#xl/styles.xml',
                    __DIR__ . '/../../../../res/simple.xlsx'
                )
            )
        );

        $this->table = new StylesTable($this->database);
    }
}
