<?php

namespace Test\KHerGe\Excel\Database;

use KHerGe\Excel\Database;
use KHerGe\Excel\Database\NumberFormatsTable;
use KHerGe\Excel\Reader\StylesReader;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the number formats table manager functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\Database\NumberFormatsTable
 */
class NumberFormatsTableTest extends TestCase
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
    private $table;

    /**
     * Verify that the number formats are imported.
     */
    public function testImportTheNumberFormats()
    {
        $reader = new StylesReader(
            sprintf(
                'zip://%s#xl/styles.xml',
                __DIR__ . '/../../../../res/simple.xlsx'
            )
        );

        $this->table->import($reader);

        $select = 'SELECT format FROM formats WHERE id = 8;';

        self::assertEquals(
            '"$"#,##0.00_);[Red]\("$"#,##0.00\)',
            $this
                ->database
                ->column($select),
            'The number formats were not imported.'
        );
    }

    /**
     * Creates a new shared strings table manager.
     */
    protected function setUp()
    {
        $this->database = Database::create();

        $this->table = new NumberFormatsTable($this->database);
    }
}
