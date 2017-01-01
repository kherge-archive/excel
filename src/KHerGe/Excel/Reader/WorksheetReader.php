<?php

namespace KHerGe\Excel\Reader;

use Iterator;
use KHerGe\Excel\Reader\Worksheet\CellInfo;
use KHerGe\XML\Node\NodeInterface;

/**
 * Reads an XML document containing worksheet information.
 *
 * This class will iterate through the worksheet information that is stored in
 * a worksheet XML file. Each piece of information is returned as an instance
 * of a class that best represents the data.
 *
 * ```php
 * $reader = new WorksheetReader('/path/to/worksheet.xml');
 *
 * foreach ($reader as $path => $info) {
 *     // ...
 * }
 * ```
 *
 * The following information is supported:
 *
 * - `KHerGe\Excel\Reader\Worksheet\CellInfo` - Represents an individual cell
 *   in a worksheet. This will need to be used with other reader classes in
 *   order to fully resolve the value of the cell.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class WorksheetReader implements Iterator
{
    /**
     * The path to the workbook XML file.
     *
     * @var string
     */
    private $file;

    /**
     * The current workbook information.
     *
     * @var null|object
     */
    private $info;

    /**
     * The node path of where the information was collected.
     *
     * @var null|string
     */
    private $path;

    /**
     * The advanced XML file reader.
     *
     * @var AdvancedFileReader|null
     */
    private $reader;

    /**
     * Initializes the new workbook reader.
     *
     * @param string $file The path to the workbook XML file.
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Returns the current workbook information.
     *
     * @return null|object The workbook information.
     */
    public function current()
    {
        return $this->info;
    }

    /**
     * Returns the node path of where the information was collected.
     *
     * @return null|string The path to the node.
     */
    public function key()
    {
        return $this->path;
    }

    /**
     * Reads the next set of information.
     */
    public function next()
    {
        $this->info = null;

        if (null === $this->reader) {
            return;
        }

        while ($this->reader->valid()) {
            $node = $this->reader->current();
            $path = $this->reader->key();

            if ($node->isElement() && $node->isStart()) {
                if ((0 === strpos($path, '/worksheet/sheetData/row'))
                    && ('c' === $node->getLocalName())) {
                    $this->path = $path;

                    $this->readCell($node);
                }
            }

            $this->reader->next();

            if (null !== $this->info) {
                break;
            }
        }
    }

    /**
     * Resets the iterator and rewinds the XML document.
     */
    public function rewind()
    {
        $this->info = null;
        $this->path = null;

        $this->reader = new AdvancedFileReader($this->file);
        $this->reader->rewind();

        $this->next();
    }

    /**
     * Checks if workbook information was read from the file.
     *
     * @return boolean Returns `true` if there was, `false` if not.
     */
    public function valid()
    {
        return (null !== $this->info);
    }

    /**
     * Reads the cell information from the worksheet.
     *
     * @param NodeInterface $node The node representation.
     */
    private function readCell(NodeInterface $node)
    {
        preg_match(
            '/^([A-Z]+)([0-9]+)$/',
            $node->getAttribute('r'),
            $name
        );

        $this->info = new CellInfo(
            $name[1],
            (int) $name[2],
            $this->reader->readTextContent(),
            $node->hasAttribute('t')
                ? $node->getAttribute('t')
                : null,
            $node->hasAttribute('s')
                ? (int) $node->getAttribute('s')
                : null
        );
    }
}
