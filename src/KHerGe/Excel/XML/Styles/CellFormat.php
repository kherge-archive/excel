<?php

namespace KHerGe\Excel\XML\Styles;

/**
 * Represents an individual cell format in the workbook.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class CellFormat
{
    /**
     * The unique identifier.
     *
     * @var integer
     */
    private $id;

    /**
     * The apply number format flag.
     *
     * @var boolean
     */
    private $numberFormat;

    /**
     * The number format unique identifier.
     *
     * @var integer
     */
    private $numberFormatId;

    /**
     * Initializes the new cell format representation.
     *
     * @param integer $id             The unique identifier.
     * @param boolean $numberFormat   The apply number format flag.
     * @param integer $numberFormatId The number format unique identifier.
     */
    public function __construct($id, $numberFormat, $numberFormatId)
    {
        $this->id = $id;
        $this->numberFormat = $numberFormat;
        $this->numberFormatId = $numberFormatId;
    }

    /**
     * Returns the unique identifier.
     *
     * @return integer The unique identifier.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the unique identifier for the number format.
     *
     * @return integer The unique identifier.
     */
    public function getNumberFormatId()
    {
        return $this->numberFormatId;
    }

    /**
     * Checks if a number format should be applied.
     *
     * @return boolean Returns `true` if it should, `false` if not.
     */
    public function isNumberFormat()
    {
        return $this->numberFormat;
    }
}
