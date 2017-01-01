<?php

namespace Test\KHerGe\Excel\Reader\Styles;

use KHerGe\Excel\Reader\Styles\NumberFormatInfo;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the number format information manager functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\Reader\Styles\NumberFormatInfo
 */
class NumberFormatInfoTest extends TestCase
{
    /**
     * The format code.
     *
     * @var string
     */
    private $formatCode = 'test';

    /**
     * The unique identifier for the number format.
     *
     * @var integer
     */
    private $id = 123;

    /**
     * The number format information manager.
     *
     * @var NumberFormatInfo
     */
    private $info;

    /**
     * Verify that the unique identifier for the number format is returned.
     */
    public function testGetTheUniqueIdentifierForTheNumberFormat()
    {
        self::assertEquals(
            $this->id,
            $this->info->getId(),
            'The unique identifier was not returned.'
        );
    }

    /**
     * Verify that the format code is returned.
     */
    public function testGetTheFormatCodeForTheNumberFormat()
    {
        self::assertEquals(
            $this->formatCode,
            $this->info->getFormatCode(),
            'The format code was not returned.'
        );
    }

    /**
     * Creates a new number format information manager.
     */
    protected function setUp()
    {
        $this->info = new NumberFormatInfo(
            $this->id,
            $this->formatCode
        );
    }
}
