<?php

namespace KHerGe\Excel;

use DateInterval;
use DateTime;

/**
 * Decodes the raw value of a cell in a worksheet.
 *
 * This class is responsible for properly decoding a raw value that has been
 * stored in a worksheet. This includes recognizing date and time formats and
 * processing the values correctly.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Decoder
{
    /**
     * The leap year bug in Excel.
     *
     * @link https://support.microsoft.com/en-us/kb/214326
     *
     * @var integer
     */
    const KB_214326 = 60;

    /**
     * The number of seconds in a day.
     *
     * @var integer
     */
    const TIME_DAY = 86400;

    /**
     * The number of seconds in an hour.
     *
     * @var integer
     */
    const TIME_HOUR = 3600;

    /**
     * The number of seconds in a minute.
     *
     * @var integer
     */
    const TIME_MINUTE = 60;

    /**
     * The boolean type.
     *
     * @var string
     */
    const TYPE_BOOLEAN = 'b';

    /**
     * The ISO 8601 date type.
     *
     * @var string
     */
    const TYPE_DATE = 'd';

    /**
     * The error type.
     *
     * @var string
     */
    const TYPE_ERROR = 'e';

    /**
     * The inline string type.
     *
     * @var string
     */
    const TYPE_INLINE_STRING = 'inlineStr';

    /**
     * The number type.
     *
     * @var string
     */
    const TYPE_NUMBER = 'n';

    /**
     * The shared string type.
     *
     * @var string
     */
    const TYPE_SHARED_STRING = 's';

    /**
     * The formula string type.
     *
     * @var string
     */
    const TYPE_FORMULA = 'str';

    /**
     * The date and time format characters.
     *
     * @var string[]
     */
    private static $dateTimeCharacters = ['e', 'd', 'h', 'm', 's', 'yy'];

    /**
     * The formats recognized as date or time formats.
     *
     * @var boolean[]
     */
    private $dateTimeFormats = [];

    /**
     * Decodes the value of a cell using the raw cell information.
     *
     * This method will use the raw cell information as returned by the
     * `CellsTable` manager to decode the raw value of a cell. The array
     * is expected to be the following format:
     *
     * ```php
     * $cell = [
     *     'type' => null,   // The type of the cell.
     *     'value' => null,  // The raw value of the cell.
     *     'shared' => null, // The shared string found.
     *     'format' => null, // The format of the value.
     * ];
     * ```
     *
     * @param array $cell The raw cell information.
     *
     * @return boolean|DateInterval|DateTime|float|integer|null The decoded value.
     */
    public function decode(array $cell)
    {
        if (null === $cell['type']) {
            $cell['type'] = self::TYPE_NUMBER;
        }

        switch ($cell['type']) {
            case self::TYPE_BOOLEAN:
                return ('1' === $cell['value']);

            case self::TYPE_DATE:
                return DateTime::createFromFormat(
                    DateTime::ISO8601,
                    $cell['value']
                );

            case self::TYPE_NUMBER:
                return $this->decodeNumber($cell);

            case self::TYPE_SHARED_STRING:
                return $cell['shared'];
        }

        return $cell['value'];
    }

    /**
     * Decodes a date value.
     *
     * @param float $value The date value.
     *
     * @return DateTime The decoded date.
     */
    private function decodeDate($value)
    {
        $date = intval($value);

        $time = $value - $date;
        $time *= self::TIME_DAY;
        $time = round($time, 0);

        $instance = DateTime::createFromFormat('|Y-m-d', '1899-12-31');
        $instance->modify("+$date days");
        $instance->modify("+$time seconds");

        return $instance;
    }

    /**
     * Decodes a date or time value.
     *
     * @param string $value The raw value of the cell.
     *
     * @return DateInterval|DateTime The date or time.
     */
    private function decodeDateTime($value)
    {
        $value = floatval($value);

        if (ceil($value) > self::KB_214326) {
            $value -= 1;
        }

        if (1 <= $value) {
            return $this->decodeDate($value);
        }

        return $this->decodeTime($value);
    }

    /**
     * Decodes a value that is a number type.
     *
     * @param array $cell The raw cell information.
     *
     * @return DateInterval|DateTime|float|integer|null The decoded number.
     */
    private function decodeNumber(array $cell)
    {
        if (null === $cell['value']) {
            return null;
        }

        if (isset($cell['format'])
            && $this->isDateTimeFormat($cell['format'])) {
            return $this->decodeDateTime($cell['value']);
        }

        return (false === strpos($cell['value'], '.'))
            ? intval($cell['value'])
            : floatval($cell['value']);
    }

    /**
     * Decodes a time value.
     *
     * @param float $value The time value.
     *
     * @return DateInterval The decoded time.
     */
    private function decodeTime($value)
    {
        $seconds = round(self::TIME_DAY * $value);

        $hours = floor($seconds / self::TIME_HOUR);
        $seconds -= $hours * self::TIME_HOUR;

        $minutes = floor($seconds / self::TIME_MINUTE);
        $seconds -= $minutes * self::TIME_MINUTE;

        return new DateInterval("PT{$hours}H{$minutes}M{$seconds}S");
    }

    /**
     * Checks if a number format is a date or time format.
     *
     * @param string $format The format to check.
     *
     * @return boolean Returns `true` if it is or `false` if not.
     */
    private function isDateTimeFormat($format)
    {
        if (!isset($this->dateTimeFormats[$format])) {
            $this->dateTimeFormats[$format] = false;

            $test = preg_replace('((?<!\\\)\[.+?(?<!\\\)\])', '', $format);

            foreach (self::$dateTimeCharacters as $character) {
                if (false !== strpos($test, $character)) {
                    $this->dateTimeFormats[$format] = true;

                    break;
                }
            }
        }

        return $this->dateTimeFormats[$format];
    }
}
