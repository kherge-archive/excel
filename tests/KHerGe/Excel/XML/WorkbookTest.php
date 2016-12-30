<?php

namespace Test\KHerGe\Excel\XML;

use KHerGe\Excel\XML\Workbook;
use KHerGe\Excel\XML\Workbook\Worksheet;
use KHerGe\XML\FileReaderFactory;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the workbook data iterator functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\XML\Workbook
 */
class WorkbookTest extends TestCase
{
    /**
     * The workbook data iterator.
     *
     * @var Workbook
     */
    private static $workbook;

    /**
     * Returns the expected worksheet names and indexes.
     *
     * @return array[] The expected worksheets.
     */
    public function getWorksheets()
    {
        return [
            [1, 'First'],
            [2, 'Second'],
            [3, 'Third']
        ];
    }

    /**
     * Verify that the worksheet name is returned with the correct index.
     *
     * @param integer $index  The expected key.
     * @param string  $name   The expected name.
     *
     * @dataProvider getWorksheets
     */
    public function testGetTheWorksheetNameWithItsIndex($index, $name)
    {
        $worksheet = self::$workbook->current();

        self::assertInstanceOf(
            Worksheet::class,
            $worksheet,
            'An instance of `Workbook\Worksheet` was not returned.'
        );

        self::assertEquals(
            $index,
            $worksheet->getIndex(),
            'The expected index key was not returned.'
        );

        self::assertEquals(
            $name,
            $worksheet->getName(),
            'The expected name was not returned.'
        );
    }

    /**
     * Creates a new workbook data iterator.
     */
    public static function setUpBeforeClass()
    {
        self::$workbook = new Workbook(
            function () {
                return (new FileReaderFactory())->open(
                    'zip://' . __DIR__ . '/../../../../res/complex.xlsx#xl/workbook.xml'
                );
            }
        );

        self::$workbook->rewind();
    }

    /**
     * Advances the iterator to the next string.
     */
    protected function tearDown()
    {
        self::$workbook->next();
    }
}
