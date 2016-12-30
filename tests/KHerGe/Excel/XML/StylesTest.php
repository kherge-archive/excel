<?php

namespace Test\KHerGe\Excel\XML;

use KHerGe\Excel\XML\Styles;
use KHerGe\Excel\XML\Styles\CellFormat;
use KHerGe\Excel\XML\Styles\NumberFormat;
use KHerGe\XML\FileReaderFactory;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that style information iterator functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\XML\Styles
 */
class StylesTest extends TestCase
{
    /**
     * The style information iterator.
     *
     * @var Styles
     */
    private static $styles;

    /**
     * Returns the list of assertions for the expected styles.
     *
     * @return array[] The assertions.
     */
    public function getAssertions()
    {
        return [

            // #0
            [
                164,
                function (NumberFormat $format) {
                    self::assertSame(
                        164,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertEquals(
                        '[$-F400]h:mm:ss\ AM/PM',
                        $format->getCode(),
                        'The formatting code was not set properly.'
                    );
                }
            ],

            // #1
            [
                165,
                function (NumberFormat $format) {
                    self::assertSame(
                        165,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertEquals(
                        '[$-409]m/d/yy\ h:mm\ AM/PM;@',
                        $format->getCode(),
                        'The formatting code was not set properly.'
                    );
                }
            ],

            // #2
            [
                166,
                function (NumberFormat $format) {
                    self::assertSame(
                        166,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertEquals(
                        '"$"#,##0.00',
                        $format->getCode(),
                        'The formatting code was not set properly.'
                    );
                }
            ],

            // #3
            [
                0,
                function (CellFormat $format) {
                    self::assertSame(
                        0,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertFalse(
                        $format->isNumberFormat(),
                        'The number format flag was not set properly.'
                    );

                    self::assertSame(
                        0,
                        $format->getNumberFormatId(),
                        'The unique identifier for the number format was not set properly.'
                    );
                }
            ],

            // #4
            [
                1,
                function (CellFormat $format) {
                    self::assertSame(
                        1,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertTrue(
                        $format->isNumberFormat(),
                        'The number format flag was not set properly.'
                    );

                    self::assertSame(
                        14,
                        $format->getNumberFormatId(),
                        'The unique identifier for the number format was not set properly.'
                    );
                }
            ],

            // #5
            [
                2,
                function (CellFormat $format) {
                    self::assertSame(
                        2,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertFalse(
                        $format->isNumberFormat(),
                        'The number format flag was not set properly.'
                    );

                    self::assertSame(
                        0,
                        $format->getNumberFormatId(),
                        'The unique identifier for the number format was not set properly.'
                    );
                }
            ],

            // #6
            [
                3,
                function (CellFormat $format) {
                    self::assertSame(
                        3,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertTrue(
                        $format->isNumberFormat(),
                        'The number format flag was not set properly.'
                    );

                    self::assertSame(
                        1,
                        $format->getNumberFormatId(),
                        'The unique identifier for the number format was not set properly.'
                    );
                }
            ],

            // #7
            [
                4,
                function (CellFormat $format) {
                    self::assertSame(
                        4,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertTrue(
                        $format->isNumberFormat(),
                        'The number format flag was not set properly.'
                    );

                    self::assertSame(
                        1,
                        $format->getNumberFormatId(),
                        'The unique identifier for the number format was not set properly.'
                    );
                }
            ],

            // #8
            [
                5,
                function (CellFormat $format) {
                    self::assertSame(
                        5,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertTrue(
                        $format->isNumberFormat(),
                        'The number format flag was not set properly.'
                    );

                    self::assertSame(
                        14,
                        $format->getNumberFormatId(),
                        'The unique identifier for the number format was not set properly.'
                    );
                }
            ],

            // #9
            [
                6,
                function (CellFormat $format) {
                    self::assertSame(
                        6,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertTrue(
                        $format->isNumberFormat(),
                        'The number format flag was not set properly.'
                    );

                    self::assertSame(
                        164,
                        $format->getNumberFormatId(),
                        'The unique identifier for the number format was not set properly.'
                    );
                }
            ],

            // #10
            [
                7,
                function (CellFormat $format) {
                    self::assertSame(
                        7,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertTrue(
                        $format->isNumberFormat(),
                        'The number format flag was not set properly.'
                    );

                    self::assertSame(
                        164,
                        $format->getNumberFormatId(),
                        'The unique identifier for the number format was not set properly.'
                    );
                }
            ],

            // #11
            [
                8,
                function (CellFormat $format) {
                    self::assertSame(
                        8,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertTrue(
                        $format->isNumberFormat(),
                        'The number format flag was not set properly.'
                    );

                    self::assertSame(
                        165,
                        $format->getNumberFormatId(),
                        'The unique identifier for the number format was not set properly.'
                    );
                }
            ],

            // #12
            [
                9,
                function (CellFormat $format) {
                    self::assertSame(
                        9,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertTrue(
                        $format->isNumberFormat(),
                        'The number format flag was not set properly.'
                    );

                    self::assertSame(
                        165,
                        $format->getNumberFormatId(),
                        'The unique identifier for the number format was not set properly.'
                    );
                }
            ],

            // #13
            [
                10,
                function (CellFormat $format) {
                    self::assertSame(
                        10,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertTrue(
                        $format->isNumberFormat(),
                        'The number format flag was not set properly.'
                    );

                    self::assertSame(
                        166,
                        $format->getNumberFormatId(),
                        'The unique identifier for the number format was not set properly.'
                    );
                }
            ],

            // #14
            [
                11,
                function (CellFormat $format) {
                    self::assertSame(
                        11,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertTrue(
                        $format->isNumberFormat(),
                        'The number format flag was not set properly.'
                    );

                    self::assertSame(
                        166,
                        $format->getNumberFormatId(),
                        'The unique identifier for the number format was not set properly.'
                    );
                }
            ],

            // #15
            [
                12,
                function (CellFormat $format) {
                    self::assertSame(
                        12,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertTrue(
                        $format->isNumberFormat(),
                        'The number format flag was not set properly.'
                    );

                    self::assertSame(
                        12,
                        $format->getNumberFormatId(),
                        'The unique identifier for the number format was not set properly.'
                    );
                }
            ],

            // #16
            [
                13,
                function (CellFormat $format) {
                    self::assertSame(
                        13,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertTrue(
                        $format->isNumberFormat(),
                        'The number format flag was not set properly.'
                    );

                    self::assertSame(
                        12,
                        $format->getNumberFormatId(),
                        'The unique identifier for the number format was not set properly.'
                    );
                }
            ],

            // #17
            [
                14,
                function (CellFormat $format) {
                    self::assertSame(
                        14,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertFalse(
                        $format->isNumberFormat(),
                        'The number format flag was not set properly.'
                    );

                    self::assertSame(
                        0,
                        $format->getNumberFormatId(),
                        'The unique identifier for the number format was not set properly.'
                    );
                }
            ],

            // #18
            [
                15,
                function (CellFormat $format) {
                    self::assertSame(
                        15,
                        $format->getId(),
                        'The unique identifier was not set properly.'
                    );

                    self::assertFalse(
                        $format->isNumberFormat(),
                        'The number format flag was not set properly.'
                    );

                    self::assertSame(
                        0,
                        $format->getNumberFormatId(),
                        'The unique identifier for the number format was not set properly.'
                    );
                }
            ],

        ];
    }

    /**
     * Verify that the style is returned with the correct key.
     *
     * @param integer  $key        The expected key.
     * @param callable $assertions The assertions to make.
     *
     * @dataProvider getAssertions
     */
    public function testVerifyTheStyleIsReturnedWithTheCorrectKey(
        $key,
        callable $assertions
    ) {
        self::assertSame(
            $key,
            self::$styles->key(),
            'The key was not returned correctly.'
        );

        $assertions(self::$styles->current());
    }

    /**
     * Creates a new style information iterator.
     */
    public static function setUpBeforeClass()
    {
        self::$styles = new Styles(
            function () {
                return (new FileReaderFactory())->open(
                    'zip://' . __DIR__ . '/../../../../res/complex.xlsx#xl/styles.xml'
                );
            }
        );

        self::$styles->rewind();
    }

    /**
     * Advances the iterator to the next style.
     */
    protected function tearDown()
    {
        self::$styles->next();
    }
}
