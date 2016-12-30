<?php

namespace Test\KHerGe\Excel\XML;

use KHerGe\Excel\XML\Worksheet;
use KHerGe\Excel\XML\Worksheet\Cell;
use KHerGe\Excel\XML\Worksheet\Column;
use KHerGe\XML\FileReaderFactory;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the worksheet data iterator functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class WorksheetTest extends TestCase
{
    /**
     * The worksheet data iterator.
     *
     * @var Worksheet
     */
    private static $worksheet;

    /**
     * Returns the assertions for each iteration of the worksheet.
     *
     * @return array[] The assertions.
     */
    public function getAssertions()
    {
        return [

            // #0
            [
                function (Column $column) {
                    self::assertNull(
                        $column->getStyleId(),
                        'There should not be a cell style unique identifier set.'
                    );
                }
            ],

            // #1
            [
                function (Column $column) {
                    self::assertNull(
                        $column->getStyleId(),
                        'There should not be a cell style unique identifier set.'
                    );
                }
            ],

            // #2
            [
                function (Column $column) {
                    self::assertNull(
                        $column->getStyleId(),
                        'There should not be a cell style unique identifier set.'
                    );
                }
            ],

            // #3
            [
                function (Column $column) {
                    self::assertNull(
                        $column->getStyleId(),
                        'There should not be a cell style unique identifier set.'
                    );
                }
            ],

            // #4
            [
                function (Column $column) {
                    self::assertNull(
                        $column->getStyleId(),
                        'There should not be a cell style unique identifier set.'
                    );
                }
            ],

            // #5
            [
                function (Column $column) {
                    self::assertNull(
                        $column->getStyleId(),
                        'There should not be a cell style unique identifier set.'
                    );
                }
            ],

            // #6
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'A',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        1,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_SHARED_STRING,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '10',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #7
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'B',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        1,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_SHARED_STRING,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '0',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #8
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'C',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        1,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_SHARED_STRING,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '1',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #9
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'D',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        1,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_SHARED_STRING,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '2',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #10
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'E',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        1,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_SHARED_STRING,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '3',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #11
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'F',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        1,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_SHARED_STRING,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '4',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #12
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'G',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        1,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_SHARED_STRING,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '6',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #13
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'H',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        1,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_SHARED_STRING,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '8',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #14
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'I',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        1,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_SHARED_STRING,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '9',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #15
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'A',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        2,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_SHARED_STRING,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '10',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #16
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'B',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        2,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_NUMERIC,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '1',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #17
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'C',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        2,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_NUMERIC,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '42732.582638888889',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #18
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'D',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        2,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_NUMERIC,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '42732',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #19
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'E',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        2,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_NUMERIC,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '0.58263888888888882',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #20
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'F',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        2,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_SHARED_STRING,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '5',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #21
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'G',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        2,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_SHARED_STRING,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '7',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #22
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'H',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        2,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_NUMERIC,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '1.39',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #23
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'I',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        2,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_NUMERIC,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '0.5',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #24
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'A',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        3,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_SHARED_STRING,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '11',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #25
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'B',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        3,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_NUMERIC,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertNull(
                        $cell->getValue(),
                        'The cell should not have any value.'
                    );
                }
            ],

            // #26
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'C',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        3,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_SHARED_STRING,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertEquals(
                        '12',
                        $cell->getValue(),
                        'The raw value of the cell was not returned.'
                    );
                }
            ],

            // #27
            [
                function (Cell $cell) {
                    self::assertEquals(
                        'C',
                        $cell->getColumn(),
                        'The name of the column was not returned.'
                    );

                    self::assertEquals(
                        4,
                        $cell->getRow(),
                        'The row number was not returned.'
                    );

                    self::assertEquals(
                        Cell::TYPE_NUMERIC,
                        $cell->getType(),
                        'The type of the cell value was not returned.'
                    );

                    self::assertNull(
                        $cell->getValue(),
                        'The cell should not have any value.'
                    );
                }
            ],

        ];
    }

    /**
     * Verify that the iteration contains the expected data.
     *
     * @param callable $assertions The assertions to make.
     *
     * @dataProvider getAssertions
     */
    public function testVerifyTheIterationContainsTheCorrectData(
        callable $assertions
    ) {
        $assertions(self::$worksheet->current());
    }

    /**
     * Creates a new worksheet data iterator.
     */
    public static function setUpBeforeClass()
    {
        self::$worksheet = new Worksheet(
            function () {
                return (new FileReaderFactory())->open(
                    'zip://' . __DIR__ . '/../../../../res/simple.xlsx#xl/worksheets/sheet1.xml'
                );
            }
        );

        self::$worksheet->rewind();
    }

    /**
     * Advances the iterator.
     */
    protected function tearDown()
    {
        self::$worksheet->next();
    }
}
