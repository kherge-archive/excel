<?php

namespace Test\KHerGe\Excel\Reader;

use KHerGe\Excel\Reader\Worksheet\CellInfo;
use KHerGe\Excel\Reader\WorksheetReader;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that worksheet information reader functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\Reader\WorksheetReader
 */
class WorksheetReaderTest extends TestCase
{
    /**
     * The worksheet information reader.
     *
     * @var WorksheetReader
     */
    private static $reader;

    /**
     * Returns the expected information from the worksheet.
     *
     * @return array[] The expected information.
     */
    public function getExpectedInformation()
    {
        return [

            // #0
            [
                '/worksheet/sheetData/row/c',
                new CellInfo('A', 1, '10', 's', 5)
            ],

            // #1
            [
                '/worksheet/sheetData/row/c[2]',
                new CellInfo('B', 1, '0', 's', 5)
            ],

            // #2
            [
                '/worksheet/sheetData/row/c[3]',
                new CellInfo('C', 1, '1', 's', 5)
            ],

            // #3
            [
                '/worksheet/sheetData/row/c[4]',
                new CellInfo('D', 1, '2', 's', 5)
            ],

            // #4
            [
                '/worksheet/sheetData/row/c[5]',
                new CellInfo('E', 1, '3', 's', 5)
            ],

            // #5
            [
                '/worksheet/sheetData/row/c[6]',
                new CellInfo('F', 1, '4', 's', 5)
            ],

            // #6
            [
                '/worksheet/sheetData/row/c[7]',
                new CellInfo('G', 1, '6', 's', 5)
            ],

            // #7
            [
                '/worksheet/sheetData/row/c[8]',
                new CellInfo('H', 1, '8', 's', 5)
            ],

            // #8
            [
                '/worksheet/sheetData/row/c[9]',
                new CellInfo('I', 1, '9', 's', 5)
            ],

            // #9
            [
                '/worksheet/sheetData/row[2]/c',
                new CellInfo('A', 2, '10', 's', null)
            ],

            // #10
            [
                '/worksheet/sheetData/row[2]/c[2]',
                new CellInfo('B', 2, '1', null, 1)
            ],

            // #11
            [
                '/worksheet/sheetData/row[2]/c[3]',
                new CellInfo('C', 2, '42732.582638888889', null, 2)
            ],

            // #12
            [
                '/worksheet/sheetData/row[2]/c[4]',
                new CellInfo('D', 2, '42732', null, 3)
            ],

            // #13
            [
                '/worksheet/sheetData/row[2]/c[5]',
                new CellInfo('E', 2, '0.58263888888888882', null, 4)
            ],

            // #14
            [
                '/worksheet/sheetData/row[2]/c[6]',
                new CellInfo('F', 2, '5', 's', null)
            ],

            // #15
            [
                '/worksheet/sheetData/row[2]/c[7]',
                new CellInfo('G', 2, '7', 's', null)
            ],

            // #16
            [
                '/worksheet/sheetData/row[2]/c[8]',
                new CellInfo('H', 2, '1.39', null, 6)
            ],

            // #17
            [
                '/worksheet/sheetData/row[2]/c[9]',
                new CellInfo('I', 2, '0.5', null, 7)
            ],

        ];
    }

    /**
     * Verify that the worksheet information is iterated through.
     *
     * @param string $path     The expected node path.
     * @param object $expected The expected information.
     *
     * @dataProvider getExpectedInformation
     */
    public function testIterateThroughTheInformationInTheWorksheet(
        $path,
        $expected
    ) {
        self::assertEquals(
            $path,
            self::$reader->key(),
            'The expected node path was not returned.'
        );

        $info = self::$reader->current();

        self::assertInstanceOf(
            get_class($expected),
            $info,
            'The expected class of information was not returned.'
        );

        if ($expected instanceof CellInfo) {
            self::assertEquals(
                $expected->getColumn(),
                $info->getColumn(),
                'The expected column name was not set.'
            );

            self::assertEquals(
                $expected->getRawValue(),
                $info->getRawValue(),
                'The expected raw value was not set.'
            );

            self::assertSame(
                $expected->getRow(),
                $info->getRow(),
                'The expected row number was not set.'
            );

            self::assertSame(
                $expected->getStyleId(),
                $info->getStyleId(),
                'The expected style unique identifier was not set.'
            );

            self::assertEquals(
                $expected->getType(),
                $info->getType(),
                'The expected cell value type was not set.'
            );
        }
    }

    /**
     * Verify that the worksheet information is not iterated if the reader is not rewound.
     */
    public function testDoNotIterateIfTheReaderIsNotRewound()
    {
        $reader = new WorksheetReader('php://memory');

        // If there is no error, we're good.
        $reader->next();
    }

    /**
     * Creates a new worksheet information reader.
     */
    public static function setUpBeforeClass()
    {
        self::$reader = new WorksheetReader(
            sprintf(
                'zip://%s#xl/worksheets/sheet1.xml',
                __DIR__ . '/../../../../res/simple.xlsx'
            )
        );

        self::$reader->rewind();
    }

    /**
     * Advances the reader to the next piece of information.
     */
    protected function tearDown()
    {
        self::$reader->next();
    }
}
