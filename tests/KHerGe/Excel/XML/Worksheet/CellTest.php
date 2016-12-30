<?php

namespace Test\KHerGe\Excel\XML\Worksheet;

use KHerGe\Excel\XML\Worksheet\Cell;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the cell representation functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\XML\Worksheet\Cell
 */
class CellTest extends TestCase
{
    /**
     * The cell representation.
     *
     * @var Cell
     */
    private $cell;

    /**
     * The name of the column.
     *
     * @var string
     */
    private $column = 'ABC';

    /**
     * The row number.
     *
     * @var integer
     */
    private $row = 123;

    /**
     * The cell style unique identifier.
     *
     * @var integer
     */
    private $styleId = 456;

    /**
     * The type of the cell value.
     *
     * @var integer
     */
    private $type = Cell::TYPE_INLINE_STRING;

    /**
     * The raw value of the cell.
     *
     * @var string
     */
    private $value = 'test';

    /**
     * Verify that the name of the column is returned.
     */
    public function testGetTheNameOfTheColumn()
    {
        self::assertEquals(
            $this->column,
            $this->cell->getColumn(),
            'The name of the column was not returned.'
        );
    }

    /**
     * Verify that the row number is returned.
     */
    public function testGetTheRowNumber()
    {
        self::assertEquals(
            $this->row,
            $this->cell->getRow(),
            'The row number was not returned.'
        );
    }

    /**
     * Verify that the unique identifier for the cell style is returned.
     */
    public function testGetTheCellStyleUniqueIdentifier()
    {
        self::assertEquals(
            $this->styleId,
            $this->cell->getStyleId(),
            'The unique identifier for the cell style was not returned.'
        );
    }

    /**
     * Verify that the type of the cell value is returned.
     */
    public function testGetTheTypeOfTheCellValue()
    {
        self::assertEquals(
            $this->type,
            $this->cell->getType(),
            'The type of the cell value was not returned.'
        );
    }

    /**
     * Verify that the raw value of the cell is returned.
     */
    public function testGetTheRawValueOfTheCell()
    {
        self::assertEquals(
            $this->value,
            $this->cell->getValue(),
            'The raw value of the cell was not returned.'
        );
    }

    /**
     * Creates a new cell representation.
     */
    protected function setUp()
    {
        $this->cell = new Cell(
            $this->column,
            $this->row,
            $this->type,
            $this->styleId,
            $this->value
        );
    }
}
