<?php

namespace Test\KHerGe\Excel\Reader\Workbook;

use KHerGe\Excel\Reader\Workbook\WorksheetInfo;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the worksheet information manager functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\Reader\Workbook\WorksheetInfo
 */
class WorksheetInfoTest extends TestCase
{
    /**
     * The index of the worksheet.
     *
     * @var integer
     */
    private $index = 123;

    /**
     * The worksheet information manager.
     *
     * @var WorksheetInfo
     */
    private $info;

    /**
     * The name of the worksheet.
     *
     * @var string
     */
    private $name = 'test';

    /**
     * Verify that the index of the worksheet is returned.
     */
    public function testGetTheIndexOfTheWorksheet()
    {
        self::assertEquals(
            $this->index,
            $this->info->getIndex(),
            'The index was not returned.'
        );
    }

    /**
     * Verify that the name of the worksheet is returned.
     */
    public function testGetTheNameOfTheWorksheet()
    {
        self::assertEquals(
            $this->name,
            $this->info->getName(),
            'The name was not returned.'
        );
    }

    /**
     * Creates a new worksheet information manager.
     */
    protected function setUp()
    {
        $this->info = new WorksheetInfo(
            $this->index,
            $this->name
        );
    }
}
