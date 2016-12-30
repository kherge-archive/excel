<?php

namespace KHerGe\Excel\XML\Worksheet;

/**
 * Represents an individual cell in a worksheet.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Cell
{
    /**
     * The cell value is a boolean (i.e. `t="b"`).
     *
     * @var integer
     */
    const TYPE_BOOLEAN = 1;

    /**
     * The cell value is a date (i.e. `t="d"`).
     *
     * @var integer
     */
    const TYPE_DATE = 2;

    /**
     * The cell value is an error (i.e. `t="e"`).
     *
     * @var integer
     */
    const TYPE_ERROR = 3;

    /**
     * The cell value is an inline string (i.e. `t="inlineStr"`).
     *
     * @var integer
     */
    const TYPE_INLINE_STRING = 4;

    /**
     * The cell value is a numeric value (i.e. `t="n"`).
     *
     * @var integer
     */
    const TYPE_NUMERIC = 5;

    /**
     * The cell value is a shared string (i.e. `t="s"`).
     *
     * @var integer
     */
    const TYPE_SHARED_STRING = 6;

    /**
     * The cell value is a string (i.e. `t="str"`).
     *
     * @var integer
     */
    const TYPE_STRING = 7;

    /**
     * The column name of the cell.
     *
     * @var string
     */
    private $column;

    /**
     * The row number of the cell.
     *
     * @var integer
     */
    private $row;

    /**
     * The cell style unique identifier.
     *
     * @var integer|null
     */
    private $styleId;

    /**
     * The type of the cell value.
     *
     * @var integer
     */
    private $type;

    /**
     * The raw value of the cell.
     *
     * @var string
     */
    private $value;

    /**
     * Initializes the new cell representation.
     *
     * @param string       $column  The column name of the cell.
     * @param integer      $row     The row number of the cell.
     * @param integer      $type    The type of the cel value.
     * @param integer|null $styleId The cell style unique identifier.
     * @param string       $value   The raw value of the cell.
     */
    public function __construct($column, $row, $type, $styleId, $value)
    {
        $this->column = $column;
        $this->row = $row;
        $this->styleId = $styleId;
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * Returns the column name of the cell.
     *
     * @return string The column name.
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Returns the row number of the cell.
     *
     * @return integer The row number.
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * Returns the cell style unique identifier.
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
     * @return integer The type.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the raw value of the cell.
     *
     * @return string The raw value.
     */
    public function getValue()
    {
        return $this->value;
    }
}
