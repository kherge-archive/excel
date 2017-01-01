<?php

namespace KHerGe\Excel\Reader;

use Iterator;
use KHerGe\Excel\Reader\Workbook\WorksheetInfo;
use KHerGe\XML\Node\NodeInterface;

/**
 * Reads an XML document containing workbook information.
 *
 * This class will iterate through information that is stored in a workbook XML
 * file. Each piece of information is returned as an instance of a class that
 * best represents the data.
 *
 * ```php
 * $reader = new WorkbookReader('/path/to/workbook.xml');
 *
 * foreach ($reader as $info) {
 *     // ...
 * }
 * ```
 *
 * The following information is supported:
 *
 * - `KHerGe\Excel\Reader\Workbook\WorksheetInfo` - Represents an individual
 *   worksheet that is listed in the workbook. This will contain information
 *   such as the index and name of the worksheet.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class WorkbookReader implements Iterator
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

            if ($node->isElement() && $node->isStart()) {
                switch ($node->getQualifiedName()) {
                    case 'sheet':
                        $this->path = $this->reader->key();

                        $this->readWorksheet($node);

                        break;
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
     * Reads the worksheet information from the workbook.
     *
     * @param NodeInterface $node The node representation.
     */
    private function readWorksheet(NodeInterface $node)
    {
        $this->info = new WorksheetInfo(
            (int) $node->getAttribute('sheetId'),
            $node->getAttribute('name')
        );
    }
}
