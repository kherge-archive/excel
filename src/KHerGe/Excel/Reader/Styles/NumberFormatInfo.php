<?php

namespace KHerGe\Excel\Reader\Styles;

/**
 * Manages the information for a number format read from the styles XML document.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class NumberFormatInfo
{
    /**
     * The unique identifier for the number format.
     *
     * @var integer
     */
    private $id;

    /**
     * The format code for the number format.
     *
     * @var string
     */
    private $formatCode;

    /**
     * Initializes the new number format information manager.
     *
     * @param integer $id         The unique identifier for the number format.
     * @param string  $formatCode The format code for the number format.
     */
    public function __construct($id, $formatCode)
    {
        $this->formatCode = $formatCode;
        $this->id = $id;
    }

    /**
     * Returns the format code for the number format.
     *
     * @return string The format code.
     */
    public function getFormatCode()
    {
        return $this->formatCode;
    }

    /**
     * Returns the unique identifier for the number format.
     *
     * @return integer The unique identifier.
     */
    public function getId()
    {
        return $this->id;
    }
}
