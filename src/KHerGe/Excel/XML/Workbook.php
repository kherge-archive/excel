<?php

namespace KHerGe\Excel\XML;

use Iterator;
use KHerGe\Excel\XML\Workbook\Worksheet;
use KHerGe\XML\ReaderInterface;
use NoRewindIterator;

/**
 * Iterates through the worksheet data in a workbook.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Workbook implements Iterator
{
    /**
     * The XML reader factory.
     *
     * @var callable
     */
    private $factory;

    /**
     * The XML reader.
     *
     * @var NoRewindIterator|ReaderInterface
     */
    private $reader;

    /**
     * The worksheet read.
     *
     * @var null|Worksheet
     */
    private $worksheet;

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
        return $this->worksheet;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->worksheet = null;

        $this->readWorksheet();
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
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
        return (null !== $this->worksheet);
    }

    /**
     * Reads the next worksheet from the workbook.
     */
    private function readWorksheet()
    {
        foreach ($this->reader as $path => $node) {
            if (0 === strpos($path, '/workbook/sheets/sheet')) {
                $this->worksheet = new Worksheet(
                    (int) $node->getAttribute('sheetId'),
                    $node->getAttribute('name')
                );

                $this->reader->next();

                break;
            }
        }
    }
}
