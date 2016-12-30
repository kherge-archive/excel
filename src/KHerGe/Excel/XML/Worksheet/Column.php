<?php

namespace KHerGe\Excel\XML\Worksheet;

/**
 * Represents an individual column in a worksheet.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Column
{
    /**
     * The unique identifier for the cell style.
     *
     * @return integer|null
     */
    private $styleId;

    /**
     * Initializes the new column representation.
     *
     * @param integer|null $styleId The unique identifier for the cell style.
     */
    public function __construct($styleId)
    {
        $this->styleId = $styleId;
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
}
