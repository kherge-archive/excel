<?php

namespace Test\KHerGe\Excel\Reader\Worksheet;

use KHerGe\Excel\Reader\Worksheet\CellInfo;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the cell information manager functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\Reader\Worksheet\CellInfo
 */
class CellInfoTest extends TestCase
{
    /**
     * The name of the column.
     *
     * @var string
     */
    private $column = 'ABC';

    /**
     * The cell information manager.
     *
     * @var CellInfo
     */
    private $info;

    /**
     * The raw value of the cell.
     *
     * @var string
     */
    private $rawValue = '123';

    /**
     * The row number.
     *
     * @var integer
     */
    private $row = 456;

    /**
     * The unique identifier for the cell style.
     *
     * @var integer
     */
    private $styleId = 789;

    /**
     * The type of the cell value.
     *
     * @var string
     */
    private $type = 's';

    /**
     * Verify that the name of the column is returned.
     */
    public function testGetTheNameOfTheColumn()
    {
        self::assertEquals(
            $this->column,
            $this->info->getColumn(),
            'The name of the column was not returned.'
        );
    }

    /**
     * Verify that the raw value is returned.
     */
    public function testGetTheRawValueOfTheCell()
    {
        self::assertEquals(
            $this->rawValue,
            $this->info->getRawValue(),
            'The raw value of the cell was not returned.'
        );
    }

    /**
     * Verify that the row number is returned.
     */
    public function testGetTheRowNumber()
    {
        self::assertEquals(
            $this->row,
            $this->info->getRow(),
            'The row number was not returned.'
        );
    }

    /**
     * Verify that the unique identifier for the cell style is returned.
     */
    public function testGetTheUniqueIdentifierForTheCellStyle()
    {
        self::assertEquals(
            $this->styleId,
            $this->info->getStyleId(),
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
            $this->info->getType(),
            'The type of the cell value was not returned.'
        );
    }

    /**
     * Creates a new cell information manager.
     */
    protected function setUp()
    {
        $this->info = new CellInfo(
            $this->column,
            $this->row,
            $this->rawValue,
            $this->type,
            $this->styleId
        );
    }
}
