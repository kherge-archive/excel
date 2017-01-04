<?php

namespace Test\KHerGe\Excel;

use DateInterval;
use DateTime;
use KHerGe\Excel\Decoder;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the raw cell value decoder functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\Decoder
 */
class DecoderTest extends TestCase
{
    /**
     * The raw cell value decoder.
     *
     * @var Decoder
     */
    private $decoder;

    /**
     * Verify that a boolean value is decoded.
     */
    public function testDecodeABooleanValue()
    {
        $true = [
            'type' => 'b',
            'value' => '1'
        ];

        self::assertTrue(
            $this->decoder->decode($true),
            'The boolean value was not decoded.'
        );

        $false = [
            'type' => 'b',
            'value' => '0'
        ];

        self::assertFalse(
            $this->decoder->decode($false),
            'The boolean value was not decoded.'
        );
    }

    /**
     * Verify that an ISO 8601 date is decoded.
     */
    public function testDecodeADateValue()
    {
        $expected = new DateTime();

        $date = [
            'type' => 'd',
            'value' => $expected->format(DateTime::ISO8601)
        ];

        self::assertEquals(
            $expected,
            $this->decoder->decode($date),
            'The decoded ISO 8601 date was not decoded.'
        );
    }

    /**
     * Verify that a date value is decoded.
     */
    public function testDecodeADateValue2()
    {
        $dateTime = [
            'type' => 'n',
            'value' => '42732',
            'format' => 'mm-dd-yy'
        ];

        self::assertEquals(
            new DateTime('2016-12-28T00:00:00'),
            $this->decoder->decode($dateTime),
            'The date and time were not decoded.'
        );
    }

    /**
     * Verify that a date and time value is decoded.
     */
    public function testDecodeADateAndTimeValue()
    {
        $dateTime = [
            'type' => 'n',
            'value' => '42732.582638888889',
            'format' => '[$-409]m/d/yy\ h:mm\ AM/PM;@'
        ];

        self::assertEquals(
            new DateTime('2016-12-28T13:59:00'),
            $this->decoder->decode($dateTime),
            'The date and time were not decoded.'
        );
    }

    /**
     * Verify that an error is decoded.
     */
    public function testDecodeAnErrorValue()
    {
        $error = [
            'type' => 'e',
            'value' => 'error'
        ];

        self::assertEquals(
            $error['value'],
            $this->decoder->decode($error),
            'The error value was not decoded.'
        );
    }

    /**
     * Verify that a formula string is decoded.
     */
    public function testDecodeAFormulaString()
    {
        $formula = [
            'type' => 'str',
            'value' => 'formula'
        ];

        self::assertEquals(
            $formula['value'],
            $this->decoder->decode($formula),
            'The formula string was not decoded.'
        );
    }

    /**
     * Verify that an inline string is decoded.
     */
    public function testDecodeAnInlineString()
    {
        $inlineString = [
            'type' => 'inlineStr',
            'value' => 'inline string'
        ];

        self::assertEquals(
            $inlineString['value'],
            $this->decoder->decode($inlineString),
            'The inline string was not decoded.'
        );
    }

    /**
     * Verify that a null value is decoded.
     */
    public function testDecodeANullValue()
    {
        $null = [
            'type' => null,
            'value' => null
        ];

        self::assertNull(
            $this->decoder->decode($null),
            'The null value was not returned.'
        );
    }

    /**
     * Verify that a number is decoded.
     */
    public function testDecodeANumber()
    {
        $integer = [
            'type' => 'n',
            'value' => '123'
        ];

        self::assertSame(
            123,
            $this->decoder->decode($integer),
            'The number was not decoded.'
        );

        $float = [
            'type' => 'n',
            'value' => '456.789'
        ];

        self::assertSame(
            456.789,
            $this->decoder->decode($float),
            'The number was not decoded.'
        );
    }

    /**
     * Verify that a shared string is decoded.
     */
    public function testDecodeASharedString()
    {
        $sharedString = [
            'type' => 's',
            'shared' => 'shared string'
        ];

        self::assertEquals(
            $sharedString['shared'],
            $this->decoder->decode($sharedString),
            'The shared string was not decoded.'
        );
    }

    /**
     * Verify that a time value is decoded.
     */
    public function testDecodeATimeValue()
    {
        $dateTime = [
            'type' => 'n',
            'value' => '0.58263888888888882',
            'format' => 'h:mm AM/PM'
        ];

        self::assertEquals(
            new DateInterval('PT13H59M0S'),
            $this->decoder->decode($dateTime),
            'The date and time were not decoded.'
        );
    }

    /**
     * Creates a new raw cell value decoder.
     */
    protected function setUp()
    {
        $this->decoder = new Decoder();
    }
}
