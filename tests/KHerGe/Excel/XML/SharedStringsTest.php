<?php

namespace Test\KHerGe\Excel\XML;

use KHerGe\Excel\XML\SharedStrings;
use KHerGe\XML\FileReaderFactory;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the shared strings iterator functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\XML\SharedStrings
 */
class SharedStringsTest extends TestCase
{
    /**
     * The shared strings iterator.
     *
     * @var SharedStrings
     */
    private static $strings;

    /**
     * Returns the expected shared strings and their keys.
     *
     * @return array[] The expected strings and keys.
     */
    public function getSharedStrings()
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
            [10, 'General']
        ];
    }

    /**
     * Verify that the shared string is returned with the correct key.
     *
     * @param integer $key    The expected key.
     * @param string  $string The expected string.
     *
     * @dataProvider getSharedStrings
     */
    public function testGetTheSharedStringWithItsKey($key, $string)
    {
        self::assertEquals(
            $key,
            self::$strings->key(),
            'The expected string key was not returned.'
        );

        self::assertEquals(
            $string,
            self::$strings->current(),
            'The expected shared string was not returned.'
        );
    }

    /**
     * Creates a new shared strings iterator.
     */
    public static function setUpBeforeClass()
    {
        self::$strings = new SharedStrings(
            function () {
                return (new FileReaderFactory())->open(
                    'zip://' . __DIR__ . '/../../../../res/simple.xlsx#xl/sharedStrings.xml'
                );
            }
        );

        self::$strings->rewind();
    }

    /**
     * Advances the iterator to the next string.
     */
    protected function tearDown()
    {
        self::$strings->next();
    }
}
