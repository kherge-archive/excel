<?php

namespace KHerGe\Excel\Reader\Styles;

/**
 * Manages the information for a cell style read from the styles XML document.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class CellStyleInfo
{
    /**
     * The unique identifier for the cell style.
     *
     * @var integer
     */
    private $id;

    /**
     * The number formatting flag.
     *
     * @var boolean
     */
    private $numberFormat;

    /**
     * The unique identifier for the number format.
     *
     * @var integer
     */
    private $numberFormatId;

    /**
     * Initializes the new cell style information manager.
     *
     * @param integer $id             The unique identifier for the cell style.
     * @param boolean $numberFormat   The number format flag.
     * @param integer $numberFormatId The unique identifier for the number format.
     */
    public function __construct($id, $numberFormat, $numberFormatId)
    {
        $this->id = $id;
        $this->numberFormat = $numberFormat;
        $this->numberFormatId = $numberFormatId;
    }

    /**
     * Returns the unique identifier for the cell style.
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
     * Checks if the number formatting flag is set.
     *
     * @return boolean Returns `true` if number formatting is set, `false` if not.
     */
    public function isNumberFormat()
    {
        return $this->numberFormat;
    }
}
