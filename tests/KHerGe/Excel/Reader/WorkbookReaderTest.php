<?php

namespace Test\KHerGe\Excel\Reader;

use KHerGe\Excel\Reader\Workbook\WorksheetInfo;
use KHerGe\Excel\Reader\WorkbookReader;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the workbook reader functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class WorkbookReaderTest extends TestCase
{
    /**
     * The workbook information reader.
     *
     * @var WorkbookReader
     */
    private static $reader;

    /**
     * Returns the information that is expected to be read from the workbook.
     *
     * @return array[] The expected information.
     */
    public function getExpectedInformation()
    {
        return [
            ['/workbook/sheets/sheet', new WorksheetInfo(1, 'First')],
            ['/workbook/sheets/sheet[2]', new WorksheetInfo(2, 'Second')],
            ['/workbook/sheets/sheet[3]', new WorksheetInfo(3, 'Third')]
        ];
    }

    /**
     * Verify that the expected information is iterated through.
     *
     * @param string $path     The expected node path.
     * @param object $expected The expected information.
     *
     * @dataProvider getExpectedInformation
     */
    public function testIterateThroughTheWorkbookInformation($path, $expected)
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

        if ($expected instanceof WorksheetInfo) {
            self::assertSame(
                $expected->getIndex(),
                $info->getIndex(),
                'The expected index of the worksheet was not returned.'
            );

            self::assertEquals(
                $expected->getName(),
                $info->getName(),
                'The expected name of the worksheet was not returned.'
            );
        }
    }

    /**
     * Verify that the workbook information is not iterated if the reader is not rewound.
     */
    public function testDoNotIterateIfTheReaderIsNotRewound()
    {
        $reader = new WorkbookReader('php://memory');

        // If there is no error, we're good.
        $reader->next();
    }

    /**
     * Creates a new workbook information reader.
     */
    public static function setUpBeforeClass()
    {
        self::$reader = new WorkbookReader(
            sprintf(
                'zip://%s#xl/workbook.xml',
                __DIR__ . '/../../../../res/complex.xlsx'
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
