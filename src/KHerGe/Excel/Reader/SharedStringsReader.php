<?php

namespace KHerGe\Excel\Reader;

use Iterator;
use KHerGe\XML\Node\NodeInterface;

/**
 * Reads an XML document containing shared strings.
 *
 * This class will iterate through each shared string in an XML document for
 * shared strings. The formatting of rich text strings will not be preserved
 * and only their text is collected.
 *
 * ```php
 * $reader = new SharedStringsReader('/path/to/sharedStrings.xml');
 *
 * foreach ($reader as $index => $string) {
 *     // ...
 * }
 * ```
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class SharedStringsReader implements Iterator
{
    /**
     * The path to the shared strings XML file.
     *
     * @var string
     */
    private $file;

    /**
     * The current index for the shared string.
     *
     * @var integer|null
     */
    private $index;

    /**
     * The advanced XML file reader.
     *
     * @var AdvancedFileReader|null
     */
    private $reader;

    /**
     * The current shared string.
     *
     * @var null|string
     */
    private $string;

    /**
     * Initializes the new shared strings reader.
     *
     * @param string $file The path to the XML file.
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Returns the current shared string.
     *
     * @return null|string The shared string.
     */
    public function current()
    {
        return $this->string;
    }

    /**
     * Returns the current index for the shared string.
     *
     * @return integer|null The index.
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * Increments the index and reads the next shared string.
     */
    public function next()
    {
        if (null === $this->reader) {
            return;
        }

        $this->index++;

        $this->reader->advanceTo(
            function (NodeInterface $node, &$stop) {
                if ($node->isElement()
                    && $node->isStart()
                    && ('si' === $node->getLocalName())) {
                    $stop = true;
                }
            }
        );

        $this->string = $this->reader->readTextContent();
    }

    /**
     * Resets the iterator and rewinds the XML document.
     */
    public function rewind()
    {
        $this->index = -1;

        $this->reader = new AdvancedFileReader($this->file);
        $this->reader->rewind();

        $this->next();
    }

    /**
     * Checks to see if a shared string was read from the file.
     *
     * @return boolean Returns `true` if one was, `false` if not.
     */
    public function valid()
    {
        return (null !== $this->string);
    }
}
