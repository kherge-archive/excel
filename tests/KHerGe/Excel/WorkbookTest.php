<?php

namespace Test\KHerGe\Excel;

use KHerGe\Excel\Exception\Workbook\NoSuchWorksheetException;
use KHerGe\Excel\Workbook;
use KHerGe\Excel\Worksheet;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the workbook manager functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\Workbook
 */
class WorkbookTest extends TestCase
{
    /**
     * The workbook manager.
     *
     * @var Workbook
     */
    private $workbook;

    /**
     * Verify that the number of worksheets is returned.
     */
    public function testCountTheNumberOfWorksheets()
    {
        self::assertEquals(
            1,
            $this->workbook->countWorksheets(),
            'The number of worksheets was not returned.'
        );
    }

    /**
     * Verify that the a worksheet is checked to exist by its index.
     */
    public function testCheckIfAWorksheetExistsByItsIndex()
    {
        self::assertTrue(
            $this->workbook->hasWorksheetByIndex(1),
            'The worksheet should exist.'
        );
    }

    /**
     * Verify that the a worksheet is checked to exist by its name.
     */
    public function testCheckIfAWorksheetExistsByItsName()
    {
        self::assertTrue(
            $this->workbook->hasWorksheetByName('First'),
            'The worksheet should exist.'
        );
    }

    /**
     * Verify that a worksheet is returned for its index.
     */
    public function testGetAWorksheetByItsIndex()
    {
        $worksheet = $this->workbook->getWorksheetByIndex(1);

        self::assertInstanceOf(
            Worksheet::class,
            $worksheet,
            'An instance of `Worksheet` was not returned.'
        );

        self::assertEquals(
            1,
            $worksheet->getIndex(),
            'The index of the worksheet was not set correctly.'
        );

        self::assertEquals(
            'First',
            $worksheet->getName(),
            'The name of the worksheet was not set correctly.'
        );
    }

    /**
     * Verify that an exception is thrown when a worksheet index does not exist.
     */
    public function testThrowAnExceptionWhenTheWorksheetIndexDoesNotExist()
    {
        $this->expectException(NoSuchWorksheetException::class);

        $this->workbook->getWorksheetByIndex(999);
    }

    /**
     * Verify that a worksheet is returned for its name.
     */
    public function testGetAWorksheetByItsName()
    {
        $worksheet = $this->workbook->getWorksheetByName('First');

        self::assertInstanceOf(
            Worksheet::class,
            $worksheet,
            'An instance of `Worksheet` was not returned.'
        );

        self::assertEquals(
            1,
            $worksheet->getIndex(),
            'The index of the worksheet was not set correctly.'
        );

        self::assertEquals(
            'First',
            $worksheet->getName(),
            'The name of the worksheet was not set correctly.'
        );
    }

    /**
     * Verify that an exception is thrown when a worksheet name does not exist.
     */
    public function testThrowAnExceptionWhenTheWorksheetNameDoesNotExist()
    {
        $this->expectException(NoSuchWorksheetException::class);

        $this->workbook->getWorksheetByName('Test');
    }

    /**
     * Verify that all worksheets are iterated through.
     */
    public function testIterateThroughWorksheets()
    {
        $iterations = [
            1 => [1, 'First']
        ];

        foreach ($this->workbook->iterateWorksheets() as $index => $worksheet) {
            self::assertArrayHasKey(
                $index,
                $iterations,
                'This worksheet was not expected.'
            );

            self::assertEquals(
                $iterations[$index][0],
                $worksheet->getIndex(),
                'The index for the worksheet was not set correctly.'
            );

            self::assertEquals(
                $iterations[$index][1],
                $worksheet->getName(),
                'The name for the worksheet was not set correctly.'
            );

            unset($iterations[$index]);
        }

        self::assertEmpty(
            $iterations,
            'Not all expected worksheets were iterated through.'
        );
    }

    /**
     * Verify that all worksheet in the workbook are listed.
     */
    public function testListAllWorksheetsInTheWorkbook()
    {
        self::assertEquals(
            [
                1 => 'First'
            ],
            $this->workbook->listWorksheets(),
            'The list of worksheets was not returned as expected.'
        );
    }

    /**
     * Creates a new workbook manager.
     */
    protected function setUp()
    {
        $this->workbook = new Workbook(
            __DIR__ . '/../../../res/simple.xlsx'
        );
    }
}
