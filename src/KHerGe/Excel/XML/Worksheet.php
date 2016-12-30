<?php

namespace KHerGe\Excel\XML;

use Iterator;
use KHerGe\Excel\XML\Worksheet\Cell;
use KHerGe\Excel\XML\Worksheet\Column;
use KHerGe\XML\Node\NodeInterface;
use KHerGe\XML\ReaderInterface;
use NoRewindIterator;

/**
 * Iterates through specific worksheet data in a workbook.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Worksheet implements Iterator
{
    /**
     * The read data.
     *
     * @var Cell|Column|null
     */
    private $data;

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
        return $this->data;
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
        $this->data = null;

        foreach ($this->reader as $node) {
            if ($node->isElement() && $node->isStart()) {
                if ('col' === $node->getLocalName()) {
                    $this->readColumn($node);

                    $this->reader->next();

                    break;
                } elseif ('c' === $node->getLocalName()) {
                    $this->readCell($node);

                    $this->reader->next();

                    break;
                }
            }
        }
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
        return (null !== $this->data);
    }

    /**
     * Reads the column data from the worksheet.
     *
     * @param NodeInterface $node The column node.
     */
    private function readColumn(NodeInterface $node)
    {
        $this->data = new Column(
            $node->hasAttribute('style')
                ? (int) $node->getAttribute('style')
                : null
        );
    }

    /**
     * Reads the cell data from the worksheet.
     *
     * @param NodeInterface $node The cell node.
     */
    private function readCell(NodeInterface $node)
    {
        preg_match(
            '/^([A-Z]+)([0-9]+)$/',
            $node->getAttribute('r'),
            $name
        );

        $style = $node->hasAttribute('s')
            ? (int) $node->getAttribute('s')
            : null;

        $this->data = new Cell(
            $name[1],
            $name[2],
            $this->readCellType($node),
            $style,
            $this->readCellValue()
        );
    }

    /**
     * Reads the type of the cell from the node.
     *
     * @param NodeInterface $node The cell node.
     *
     * @return integer The type of the cell value.
     */
    private function readCellType(NodeInterface $node)
    {
        if ($node->hasAttribute('t')) {
            switch ($node->getAttribute('t')) {
                case 'b':
                    return Cell::TYPE_BOOLEAN;
                case 'd':
                    return Cell::TYPE_DATE;
                case 'e':
                    return Cell::TYPE_ERROR;
                case 'inlineStr':
                    return Cell::TYPE_INLINE_STRING;
                case 'n':
                    return Cell::TYPE_NUMERIC;
                case 's':
                    return Cell::TYPE_SHARED_STRING;
                case 'str':
                    return Cell::TYPE_STRING;
            }
        }

        return Cell::TYPE_NUMERIC;
    }

    /**
     * Reads the value of the current cell.
     *
     * @return string The value.
     */
    private function readCellValue()
    {
        $in = false;

        foreach ($this->reader as $node) {
            if ($node->isElement()) {
                if ($node->isEnd() && ('c' === $node->getLocalName())) {
                    break;
                }

                $in = (('t' === $node->getLocalName())
                    || ('v' === $node->getLocalName()));
            } elseif ($in && $node->isText()) {
                return $node->getValue();
            }
        }

        return null;
    }
}
