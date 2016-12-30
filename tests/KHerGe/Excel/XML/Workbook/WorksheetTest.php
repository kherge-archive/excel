<?php

namespace Test\KHerGe\Excel\XML\Workbook;

use KHerGe\Excel\XML\Workbook\Worksheet;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the worksheet representation functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\XML\Workbook\Worksheet
 */
class WorksheetTest extends TestCase
{
    /**
     * The index of the worksheet.
     *
     * @var integer
     */
    private $index = 1;

    /**
     * The name of the worksheet.
     *
     * @var string
     */
    private $name = 'Test';

    /**
     * The worksheet representation.
     *
     * @var Worksheet
     */
    private $worksheet;

    /**
     * Verify that the index of the worksheet is returned.
     */
    public function testGetTheIndexOfTheWorksheet()
    {
        self::assertEquals(
            $this->index,
            $this->worksheet->getIndex(),
            'The index of the worksheet was not returned.'
        );
    }

    /**
     * Verfiy that the name of the worksheet is returend.
     */
    public function testGetTheNameOfTheWorksheet()
    {
        self::assertEquals(
            $this->name,
            $this->worksheet->getName(),
            'The name of the worksheet was not returned.'
        );
    }

    /**
     * Creates a new worksheet representation.
     */
    protected function setUp()
    {
        $this->worksheet = new Worksheet($this->index, $this->name);
    }
}
