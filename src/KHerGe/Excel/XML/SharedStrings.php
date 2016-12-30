<?php

namespace KHerGe\Excel\XML;

use Iterator;
use KHerGe\XML\ReaderInterface;
use NoRewindIterator;

/**
 * Iterates through each shared string in a workbook.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class SharedStrings implements Iterator
{
    /**
     * The XML reader factory.
     *
     * @var callable
     */
    private $factory;

    /**
     * The index of the current shared string.
     *
     * @var integer
     */
    private $index = -1;

    /**
     * The XML reader.
     *
     * @var NoRewindIterator|ReaderInterface
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
     * @param callable $factory The XML reader factory.
     */
    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->string;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->index;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->index++;

        $this->string = null;

        $this->readString();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->index = -1;

        $reader = call_user_func($this->factory);
        $reader->rewind();

        $this->reader = new NoRewindIterator($reader);

        $this->next();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return (null !== $this->string);
    }

    /**
     * Reads the next shared string from the XML document.
     */
    private function readString()
    {
        $t = false;

        foreach ($this->reader as $node) {
            if ($node->isElement()) {
                $name = $node->getLocalName();

                if ('si' === $name) {
                    if (!$node->isStart()) {
                        $this->reader->next();

                        break;
                    }

                    $this->string = '';
                } elseif ('t' === $name) {
                    $t = $node->isStart();
                }
            } elseif ($t) {
                if ($node->isText() || $node->isSignificantWhitespace()) {
                    $this->string .= $node->getValue();
                }
            }
        }
    }
}
