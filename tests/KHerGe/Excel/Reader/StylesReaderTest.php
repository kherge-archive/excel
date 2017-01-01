<?php

namespace Test\KHerGe\Excel\Reader;

use KHerGe\Excel\Reader\Styles\CellStyleInfo;
use KHerGe\Excel\Reader\Styles\NumberFormatInfo;
use KHerGe\Excel\Reader\StylesReader;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that style information reader functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\Reader\StylesReader
 */
class StylesReaderTest extends TestCase
{
    /**
     * The style information reader.
     *
     * @var StylesReader
     */
    private static $reader;

    /**
     * Returns the expected information from the style information reader.
     *
     * @return array[] The expected information.
     */
    public function getExpectedInformation()
    {
        return [

            // #0
            [
                '/styleSheet/numFmts/numFmt',
                new NumberFormatInfo(8, '"$"#,##0.00_);[Red]\("$"#,##0.00\)')
            ],

            // #1
            [
                '/styleSheet/numFmts/numFmt[2]',
                new NumberFormatInfo(164, '[$-409]m/d/yy\ h:mm\ AM/PM;@')
            ],

            // #2
            [
                '/styleSheet/cellXfs/xf',
                new CellStyleInfo(0, false, 0)
            ],

            // #3
            [
                '/styleSheet/cellXfs/xf[2]',
                new CellStyleInfo(1, true, 1)
            ],

            // #4
            [
                '/styleSheet/cellXfs/xf[3]',
                new CellStyleInfo(2, true, 164)
            ],

            // #5
            [
                '/styleSheet/cellXfs/xf[4]',
                new CellStyleInfo(3, true, 14)
            ],

            // #6
            [
                '/styleSheet/cellXfs/xf[5]',
                new CellStyleInfo(4, true, 18)
            ],

            // #7
            [
                '/styleSheet/cellXfs/xf[6]',
                new CellStyleInfo(5, false, 0)
            ],

            // #8
            [
                '/styleSheet/cellXfs/xf[7]',
                new CellStyleInfo(6, true, 8)
            ],

            // #9
            [
                '/styleSheet/cellXfs/xf[8]',
                new CellStyleInfo(7, true, 12)
            ],

            // #10
            [
                '/styleSheet/cellXfs/xf[9]',
                new CellStyleInfo(8, false, 0)]
            ,

            // #11
            [
                '/styleSheet/cellXfs/xf[10]',
                new CellStyleInfo(9, false, 0)
            ]
        ];
    }

    /**
     * Verify that the style information is iterated through.
     *
     * @param string $path     The expected node path.
     * @param object $expected The expected information.
     *
     * @dataProvider getExpectedInformation
     */
    public function testIterateThroughTheStyleInformation($path, $expected)
    {
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

        if ($expected instanceof NumberFormatInfo) {
            self::assertEquals(
                $expected->getFormatCode(),
                $info->getFormatCode(),
                'The expected format code was not set.'
            );

            self::assertSame(
                $expected->getId(),
                $info->getId(),
                'The expected unique identifier was not set.'
            );
        } elseif ($expected instanceof CellStyleInfo) {
            self::assertSame(
                $expected->getId(),
                $info->getId(),
                'The expected unique identifier was not set.'
            );

            self::assertSame(
                $expected->getNumberFormatId(),
                $info->getNumberFormatId(),
                'The expected unique identifier for the number format was not set.'
            );
        }
    }

    /**
     * Verify that the shared strings are not iterated if the reader is not rewound.
     */
    public function testDoNotIterateIfTheReaderIsNotRewound()
    {
        $reader = new StylesReader('php://memory');

        // If there is no error, we're good.
        $reader->next();
    }

    /**
     * Creates a new style information reader.
     */
    public static function setUpBeforeClass()
    {
        self::$reader = new StylesReader(
            sprintf(
                'zip://%s#xl/styles.xml',
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
