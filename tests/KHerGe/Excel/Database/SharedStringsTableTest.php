<?php

namespace Test\KHerGe\Excel\Database;

use KHerGe\Excel\Database;
use KHerGe\Excel\Database\SharedStringsTable;
use KHerGe\Excel\Reader\SharedStringsReader;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the shared strings table manager functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\Database\SharedStringsTable
 */
class SharedStringsTableTest extends TestCase
{
    /**
     * The database manager.
     *
     * @var Database
     */
    private $database;

    /**
     * The shared strings table manager.
     *
     * @var SharedStringsTable
     */
    private $table;

    /**
     * Verify that the shared strings are imported.
     */
    public function testImportTheSharedStrings()
    {
        $reader = new SharedStringsReader(
            sprintf(
                'zip://%s#xl/sharedStrings.xml',
                __DIR__ . '/../../../../res/simple.xlsx'
            )
        );

        $this->table->import($reader);

        $select = 'SELECT string FROM strings WHERE "index" = 0;';

        self::assertEquals(
            'Number',
            $this
                ->database
                ->column($select),
            'The shared strings were not imported.'
        );
    }

    /**
     * Creates a new shared strings table manager.
     */
    protected function setUp()
    {
        $this->database = Database::create();

        $this->table = new SharedStringsTable($this->database);
    }
}
