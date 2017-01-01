<?php

namespace Test\KHerGe\Excel\Reader\Styles;

use KHerGe\Excel\Reader\Styles\CellStyleInfo;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the cell style information manager functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\Reader\Styles\CellStyleInfo
 */
class CellStyleInfoTest extends TestCase
{
    /**
     * The unique identifier for the cell style.
     *
     * @var integer
     */
    private $id = 123;

    /**
     * The cell style information manager.
     *
     * @var CellStyleInfo
     */
    private $info;

    /**
     * The number formatting flag.
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
     * Verify that the unique identifier for the cell style is returned.
     */
    public function testGetTheUniqueIdentifierForTheCellStyle()
    {
        self::assertEquals(
            $this->id,
            $this->info->getId(),
            'The unique identifier was not returend.'
        );
    }

    /**
     * Verify that the number formatting flag is checked.
     */
    public function testCheckIfTheNumberFormattingFlagIsSet()
    {
        self::assertTrue(
            $this->info->isNumberFormat(),
            'The number formatting flag was not returend.'
        );
    }

    /**
     * Verify that the unique identifier for the number format is returned.
     */
    public function testGetTheUniqueIdentifierForTheNumberFormat()
    {
        self::assertEquals(
            $this->numberFormatId,
            $this->info->getNumberFormatId(),
            'The unique identifier was not returned.'
        );
    }

    /**
     * Creates a new cell style information manager.
     */
    protected function setUp()
    {
        $this->info = new CellStyleInfo(
            $this->id,
            $this->numberFormat,
            $this->numberFormatId
        );
    }
}
