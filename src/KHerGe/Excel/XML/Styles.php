<?php

namespace KHerGe\Excel\XML;

use Iterator;
use KHerGe\Excel\XML\Styles\CellFormat;
use KHerGe\Excel\XML\Styles\NumberFormat;
use KHerGe\XML\ReaderInterface;
use NoRewindIterator;

/**
 * Iterates through each cell value style in a workbook.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Styles implements Iterator
{
    /**
     * The cell style counter.
     *
     * @var integer
     */
    private $cellCounter = 0;

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
     * The style representation.
     *
     * @var CellFormat|NumberFormat
     */
    private $style;

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
        return $this->style;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->style->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->style = null;

        $this->readStyle();
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
        return (null !== $this->style);
    }

    /**
     * Reads the next style element from the XML document.
     */
    private function readStyle()
    {
        foreach ($this->reader as $path => $node) {
            if (0 === strpos($path, '/styleSheet/numFmts/numFmt')) {
                $this->style = new NumberFormat(
                    (int) $node->getAttribute('numFmtId'),
                    $node->getAttribute('formatCode')
                );

                $this->reader->next();

                break;
            } elseif (0 === strpos($path, '/styleSheet/cellXfs/xf')) {
                $this->style = new CellFormat(
                    $this->cellCounter++,
                    $node->hasAttribute('applyNumberFormat')
                        ? ('1' === $node->getAttribute('applyNumberFormat'))
                        : false,
                    (int) $node->getAttribute('numFmtId')
                );

                $this->reader->next();

                break;
            }
        }
    }
}
