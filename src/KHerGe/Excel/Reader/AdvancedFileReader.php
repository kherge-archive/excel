<?php

namespace KHerGe\Excel\Reader;

use KHerGe\Excel\Exception\Reader\InvalidNodePositionException;
use KHerGe\XML\FileReader;
use KHerGe\XML\Node\NodeBuilderFactory;
use KHerGe\XML\Node\NodeBuilderFactoryInterface;
use KHerGe\XML\Node\NodeInterface;
use KHerGe\XML\Node\PathBuilderFactory;
use KHerGe\XML\Node\PathBuilderFactoryInterface;

/**
 * Provides an high level implementation of an XML file reader.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class AdvancedFileReader extends FileReader
{
    /**
     * Initializes the new advanced XML file reader.
     *
     * @param string                           $file               The path to the file.
     * @param integer                          $flags              The libxml flags.
     * @param null|PathBuilderFactoryInterface $pathBuilderFactory The node path builder factory.
     * @param NodeBuilderFactoryInterface|null $nodeBuilderFactory The node builder factory.
     */
    public function __construct(
        $file,
        $flags = 0,
        PathBuilderFactoryInterface $pathBuilderFactory = null,
        NodeBuilderFactoryInterface $nodeBuilderFactory = null
    ) {
        if (null === $nodeBuilderFactory) {
            $nodeBuilderFactory = new NodeBuilderFactory();
        }

        if (null === $pathBuilderFactory) {
            $pathBuilderFactory = new PathBuilderFactory();
        }

        parent::__construct(
            $file,
            $flags,
            $pathBuilderFactory,
            $nodeBuilderFactory
        );
    }

    /**
     * Advances the iterator until a callable is satisfied.
     *
     * This method will advance the iterator until a callable with the
     * following signature is satisfied:
     *
     * ```php
     * function ($path, NodeInterface $node, &$stop) {
     *     // ...
     * }
     * ```
     *
     * The `$path` parameter is the current element path. The `$node` parameter
     * is the current node representation. The `&$stop` parameter is a boolean
     * flag used to stop the iterator advancement.
     *
     * ```php
     * $this->advanceTo(
     *     function (NodeInterface $node, &$stop, $path) {
     *         if ($node->isElement()) {
     *             if ('example' === $node->getLocalName()) {
     *                 $stop = true;
     *             }
     *         }
     *     }
     * );
     * ```
     *
     * @param callable $finder The callable to satisfy.
     */
    public function advanceTo(callable $finder)
    {
        if (!$this->valid()) {
            return;
        }

        $this->next();

        while ($this->valid()) {
            $stop = false;

            $finder($this->current(), $stop, $this->key());

            if ($stop) {
                break;
            }

            $this->next();
        }
    }

    /**
     * Iterates through the child nodes of the current element node.
     *
     * This method will iterate through all of the child nodes of the current
     * element node until the `$handler` is satisfied or the element ending tag
     * is reached. The `$handler` is expected to have the following signature:
     *
     * ```php
     * function (NodeInterface $node, &$stop, $path) {
     *     // ...
     * }
     * ```
     *
     * If `$stop` is set to `true` by the handler, iteration will stop once the
     * handler returns. The `$node` parameter is the current node. The `$path`
     * parameter is the current path to the node.
     *
     * @param callable $handler The callable to handle each iteration.
     *
     * @throws InvalidNodePositionException If the reader is not at an element node.
     */
    public function iterateChildren(callable $handler)
    {
        if (!$this->valid()) {
            return;
        }

        $node = $this->current();

        if (!$node->isElement()) {
            throw new InvalidNodePositionException(
                'The reader must be currently at an element node.'
            );
        }

        if ($node->isEnd()) {
            if (!$node->isStart()) {
                throw new InvalidNodePositionException(
                    'The reader must be current at an element starting tag.'
                );
            }

            return;
        }

        $depth = $node->getDepth();
        $name = $node->getQualifiedName();

        $this->next();

        while ($this->valid()) {
            $node = $this->current();

            if ($node->isElement()
                && $node->isEnd()
                && ($node->getDepth() === $depth)
                && ($node->getQualifiedName() === $name)) {
                break;
            }

            $stop = false;

            $handler($node, $stop, $this->key());

            if ($stop) {
                break;
            }

            $this->next();
        }
    }

    /**
     * Reads the text content of the current node.
     *
     * This method will read the value of the current node and all of its
     * children. It is important to understand that this will advance the
     * XML reader and will discard all other data for the child nodes, if
     * there are any.
     *
     * ```php
     * $content = $this->readTextContent();
     * ```
     *
     * @return null|string The text content of the node.
     */
    public function readTextContent()
    {
        $value = '';

        $this->iterateChildren(
            function (NodeInterface $node) use (&$value) {
                if ($node->isText() || $node->isSignificantWhitespace()) {
                    $value .= $node->getValue();
                }
            }
        );

        return ('' === $value) ? null : $value;
    }

    /**
     * Reads the value of the current node.
     *
     * This method will read the value of the current node, excluding its
     * children. It is important to understand that this will advance the
     * XML reader and will discard all of the child nodes, if there are any.
     *
     * ```php
     * $value = $this->readValue();
     * ```
     *
     * @return null|string The value of the node.
     *
     * @throws InvalidNodePositionException If the reader is at an invalid position.
     */
    public function readValue()
    {
        $depth = $this->current()->getDepth() + 1;
        $value = '';

        $this->iterateChildren(
            function (NodeInterface $node) use ($depth, &$value) {
                if ($node->getDepth() === $depth) {
                    if ($node->isText() || $node->isSignificantWhitespace()) {
                        $value .= $node->getValue();
                    }
                }
            }
        );

        return ('' === $value) ? null :$value;
    }
}
