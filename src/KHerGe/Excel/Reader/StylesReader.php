<?php

namespace KHerGe\Excel\Reader;

use Iterator;
use KHerGe\Excel\Reader\Styles\CellStyleInfo;
use KHerGe\Excel\Reader\Styles\NumberFormatInfo;
use KHerGe\XML\Node\NodeInterface;

/**
 * Reads an XML document containing styling information.
 *
 * This class will iterate through the style information in the workbook.
 * Each piece of information is returned as an instance of a class that best
 * represents the data.
 *
 * ```php
 * $reader = new StylesReader('/path/to/styles.xml');
 *
 * foreach ($reader as $path => $info) {
 *     // ...
 * }
 * ```
 *
 * The following information is supported:
 *
 * - `KHerGe\Excel\Reader\Styles\CellStyleInfo` - Represents the styling
 *   information for a cell. This can be used with `NumberFormatInfo` to
 *   resolve the styling information for a cell.
 * - `KHerGe\Excel\Reader\Styles\NumberFormatInfo` - Represents a number
 *   format. This can be used with `CellStyleInfo` to resolve the styling
 *   information for a cell.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class StylesReader implements Iterator
{
    /**
     * The cell style counter.
     *
     * @var integer
     */
    private $cellStyleCounter = 0;

    /**
     * The path to the styling XML file.
     *
     * @var string
     */
    private $file;

    /**
     * The current styling information.
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
     * Initializes the new styling reader.
     *
     * @param string $file The path to the XML file.
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Returns the current styling information.
     *
     * @return null|object The styling information.
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
                if (0 === strpos($path, '/styleSheet/numFmts/numFmt')) {
                    $this->path = $path;

                    $this->readNumberFormat($node);
                } elseif ((0 === strpos($path, '/styleSheet/cellXfs'))
                    && ('xf' === $node->getLocalName())) {
                    $this->path = $path;

                    $this->readCellStyle($node);
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
     * Checks if styling information was read from the file.
     *
     * @return boolean Returns `true` if there was, `false` if not.
     */
    public function valid()
    {
        return (null !== $this->info);
    }

    /**
     * Reads the cell style information from the file.
     *
     * @param NodeInterface $node The node representation.
     */
    private function readCellStyle(NodeInterface $node)
    {
        $this->info = new CellStyleInfo(
            $this->cellStyleCounter++,
            $node->hasAttribute('applyNumberFormat')
                ? ('1' === $node->getAttribute('applyNumberFormat'))
                : false,
            (int) $node->getAttribute('numFmtId')
        );
    }

    /**
     * Reads the number format style information from the file.
     *
     * @param NodeInterface $node The node representation.
     */
    private function readNumberFormat(NodeInterface $node)
    {
        $this->info = new NumberFormatInfo(
            (int) $node->getAttribute('numFmtId'),
            $node->getAttribute('formatCode')
        );
    }
}
