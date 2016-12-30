<?php

namespace Test\KHerGe\Excel\XML\Style;

use KHerGe\Excel\XML\Styles\CellFormat;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the cell format representation functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\XML\Styles\CellFormat
 */
class CellFormatTest extends TestCase
{
    /**
     * The cell format representation.
     *
     * @var CellFormat
     */
    private $cellFormat;

    /**
     * The unique identifier for the cell format.
     *
     * @var integer
     */
    private $id = 123;

    /**
     * The flag to apply the number format.
     *
     * @var boolean
     */
    private $numberFormat = true;

    /**
     * The unique identifier for the number format.
     *
     * @var integer
     */
    private $numberFormatId = 456;

    /**
     * Verify that the unique identifier of the cell format is returned.
     */
    public function testGetTheUniqueIdentifier()
    {
        self::assertEquals(
            $this->id,
            $this->cellFormat->getId(),
            'The unique identifier was not returned.'
        );
    }

    /**
     * Verify that the number format flag is returned.
     */
    public function testGetTheApplyNumberFormatFlag()
    {
        self::assertSame(
            $this->numberFormat,
            $this->cellFormat->isNumberFormat(),
            'The apply number format flag was not returned.'
        );
    }

    /**
     * Verify that the unique identifier for the number format is returned.
     */
    public function testGetTheNumberFormatUniqueIdentifier()
    {
        self::assertEquals(
            $this->numberFormatId,
            $this->cellFormat->getNumberFormatId(),
            'The unique identifier for the number format was not returned.'
        );
    }

    /**
     * Creates a new cell format representation.
     */
    protected function setUp()
    {
        $this->cellFormat = new CellFormat(
            $this->id,
            $this->numberFormat,
            $this->numberFormatId
        );
    }
}
