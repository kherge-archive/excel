<?php

namespace KHerGe\Excel\XML\Workbook;

/**
 * Represents an individual worksheet in a workbook.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Worksheet
{
    /**
     * The index of the worksheet.
     *
     * @var integer
     */
    private $index;

    /**
     * The name of the worksheet.
     *
     * @var string
     */
    private $name;

    /**
     * Initializes the new worksheet representation.
     *
     * @param integer $index The index of the worksheet.
     * @param string  $name  The name of the worksheet.
     */
    public function __construct($index, $name)
    {
        $this->index = $index;
        $this->name = $name;
    }

    /**
     * Returns the index of the worksheet.
     *
     * @return integer The index.
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Returns the name of the worksheet.
     *
     * @return string The name.
     */
    public function getName()
    {
        return $this->name;
    }
}
