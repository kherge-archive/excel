<?php

namespace KHerGe\Excel\XML\Styles;

/**
 * Represents an individual number format in the workbook.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class NumberFormat
{
    /**
     * The unique identifier.
     *
     * @var integer
     */
    private $id;

    /**
     * The formatting code.
     *
     * @var string
     */
    private $code;

    /**
     * Initializes the new number format representation.
     *
     * @param integer $id   The unique identifier.
     * @param string  $code The formatting code.
     */
    public function __construct($id, $code)
    {
        $this->code = $code;
        $this->id = $id;
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
     * Returns the formatting code.
     *
     * @return string The formatting code.
     */
    public function getCode()
    {
        return $this->code;
    }
}
