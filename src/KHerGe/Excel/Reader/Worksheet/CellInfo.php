<?php

namespace KHerGe\Excel\Reader\Worksheet;

/**
 * Manages the information for a cell read from the worksheet.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class CellInfo
{
    /**
     * The name of the column.
     *
     * @var string
     */
    private $column;

    /**
     * The raw value of the cell.
     *
     * @var null|string
     */
    private $rawValue;

    /**
     * The row number.
     *
     * @var integer
     */
    private $row;

    /**
     * The unique identifier for the cell style.
     *
     * @var integer|null
     */
    private $styleId;

    /**
     * The type of the cell value.
     *
     * @var null|string
     */
    private $type;

    /**
     * Initialize the new cell information manager.
     *
     * @param string       $column   The name of the column.
     * @param integer      $row      The row number.
     * @param string       $rawValue The raw value of the cell.
     * @param null|string  $type     The type of the cell value.
     * @param integer|null $styleId  The unique identifier for the cell style.
     */
    public function __construct($column, $row, $rawValue, $type, $styleId)
    {
        $this->column = $column;
        $this->row = $row;
        $this->styleId = $styleId;
        $this->type = $type;
        $this->rawValue = $rawValue;
    }

    /**
     * Returns the name of the column.
     *
     * @return string The name.
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Returns the raw value of the cell.
     *
     * @return null|string The raw value.
     */
    public function getRawValue()
    {
        return $this->rawValue;
    }

    /**
     * Returns the row number.
     *
     * @return integer The number.
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * Returns the unique identifier for the cell style.
     *
     * @return integer|null The unique identifier.
     */
    public function getStyleId()
    {
        return $this->styleId;
    }

    /**
     * Returns the type of the cell value.
     *
     * @return null|string The type.
     */
    public function getType()
    {
        return $this->type;
    }
}
