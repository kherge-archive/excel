<?php

namespace Test\KHerGe\Excel\Reader;

use KHerGe\Excel\Reader\SharedStringsReader;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the shared strings reader functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\Reader\SharedStringsReader
 */
class SharedStringsReaderTest extends TestCase
{
    /**
     * The shared strings reader.
     *
     * @var SharedStringsReader
     */
    private static $reader;

    /**
     * Returns the expected shared strings.
     *
     * @return array[] The shared strings.
     */
    public function getExpectedSharedStrings()
    {
        return [
            [0, 'Number'],
            [1, 'DateTime'],
            [2, 'Date'],
            [3, 'Time'],
            [4, 'Shared String'],
            [5, 'This is a shared string.'],
            [6, 'Rich String'],
            [7, 'This is a rich text string.'],
            [8, 'Money'],
            [9, 'Fraction'],
            [10, 'General'],
            [11, 'Column Span'],
            [12, 'Row Span']
        ];
    }

    /**
     * Verifies that a shared string is returned by the reader.
     *
     * @param integer $index  The expected index of the shared string.
     * @param string  $string The expected shared string.
     *
     * @dataProvider getExpectedSharedStrings
     */
    public function testIterateTheSharedStrings($index, $string)
    {
        self::assertEquals(
            $index,
            self::$reader->key(),
            'The expected index was not returned.'
        );

        self::assertEquals(
            $string,
            self::$reader->current(),
            'The expected shared string was not returned.'
        );
    }

    /**
     * Creates a new shared strings reader.
     */
    public static function setUpBeforeClass()
    {
        self::$reader = new SharedStringsReader(
            sprintf(
                'zip://%s#xl/sharedStrings.xml',
                __DIR__ . '/../../../../res/simple.xlsx'
            )
        );

        self::$reader->rewind();
    }

    /**
     * Advances the reader to the next string.
     */
    protected function tearDown()
    {
        self::$reader->next();
    }
}
