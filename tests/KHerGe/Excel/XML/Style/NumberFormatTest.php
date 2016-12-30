<?php

namespace Test\KHerGe\Excel\XML\Style;

use KHerGe\Excel\XML\Styles\NumberFormat;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the number format representation functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\XML\Styles\NumberFormat
 */
class NumberFormatTest extends TestCase
{
    /**
     * The formatting code.
     *
     * @var string
     */
    private $code = 'test';

    /**
     * The unique identifier.
     *
     * @var integer
     */
    private $id = 123;

    /**
     * The number format representation.
     *
     * @var NumberFormat
     */
    private $numberFormat;

    /**
     * Verify that the unique identifier is returned.
     */
    public function testGetTheUniqueIdentifier()
    {
        self::assertEquals(
            $this->id,
            $this->numberFormat->getId(),
            'The unique identifier was not returned.'
        );
    }

    /**
     * Verify that the formatting code is returned.
     */
    public function testGetTheFormattingCode()
    {
        self::assertEquals(
            $this->code,
            $this->numberFormat->getCode(),
            'The formatting code was not returned.'
        );
    }

    /**
     * Creates a new number format representation.
     */
    protected function setUp()
    {
        $this->numberFormat = new NumberFormat($this->id, $this->code);
    }
}
