<?php

namespace Test\KHerGe\Excel\Reader;

use KHerGe\Excel\Exception\Reader\InvalidNodePositionException;
use KHerGe\Excel\Reader\AdvancedFileReader;
use KHerGe\XML\Node\NodeInterface;
use PHPUnit_Framework_TestCase as TestCase;

use function KHerGe\File\remove;
use function KHerGe\File\temp_file;

/**
 * Verifies that the advanced XML file reader functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\Reader\AdvancedFileReader
 */
class AdvancedFileReaderTest extends TestCase
{
    /**
     * The temporary test XML file.
     *
     * @var string
     */
    private $file;

    /**
     * The advanced file reader.
     *
     * @var AdvancedFileReader
     */
    private $reader;

    /**
     * Verify that the iterator is advanced to a callable's satisfaction.
     */
    public function testAdvanceIteratorToSatisfyCallable()
    {
        $this->reader->advanceTo(
            function (NodeInterface $node, &$stop, $path) {
                if ('child' === $node->getLocalName()) {
                    self::assertEquals(
                        '/root/child',
                        $path,
                        'The expected path was not returned.'
                    );

                    $stop = true;
                }
            }
        );

        self::assertEquals(
            'child',
            $this->reader->current()->getLocalName(),
            'The reader was not advanced to the expected node.'
        );
    }

    /**
     * Verify that the iterator is not advanced if it is at the end of the document.
     */
    public function testDoNotAdvanceTheIteratorPastTheEndOfTheDocument()
    {
        while ($this->reader->valid()) {
            $this->reader->next();
        }

        $this->reader->advanceTo(
            function () {
                self::fail(
                    'The iterator should not have been advanced.'
                );
            }
        );
    }

    /**
     * Verify that the child nodes of an element are iterated.
     */
    public function testIterateThroughTheChildrenOfAnElement()
    {
        $iterated = 0;
        $iteration = [
            [1, '/root/#text'],
            [1, '/root/child'],
            [2, '/root/child/#text'],
            [1, '/root/child'],
            [1, '/root/#text[2]'],
            [1, '/root/child[2]'],
            [1, '/root/#text[3]']
        ];

        $this->reader->iterateChildren(
            function (
                NodeInterface $node,
                &$stop,
                $path
            ) use (
                &$iterated,
                $iteration
            ) {
                self::assertEquals(
                    $iteration[$iterated][0],
                    $node->getDepth(),
                    'The expected depth was not returned.'
                );

                self::assertEquals(
                    $iteration[$iterated][1],
                    $path,
                    'The expected path was not returned.'
                );

                $iterated++;
            }
        );
    }

    /**
     * @depends testIterateThroughTheChildrenOfAnElement
     *
     * Verify that child iteration is stopped.
     */
    public function testStopMidChildIteration()
    {
        $this->reader->iterateChildren(
            function (NodeInterface $node, &$stop) {
                $stop = true;
            }
        );

        self::assertEquals(
            '#text',
            $this->reader->current()->getLocalName(),
            'The iteration was not stopped.'
        );
    }

    /**
     * Verify that the child nodes are not iterated if it is at the end of the document.
     */
    public function testDoNotIterateChildNodesPastTheEndOfTheDocument()
    {
        while ($this->reader->valid()) {
            $this->reader->next();
        }

        $this->reader->iterateChildren(
            function () {
                self::fail('The child nodes should not have been iterated.');
            }
        );
    }

    /**
     * Verify that the child nodes are not iterated if the element is empty.
     */
    public function testDoNotIterateChildNodesIfTheNodeIsEmpty()
    {
        while ($this->reader->valid()) {
            if ('/root/child[2]' === $this->reader->key()) {
                break;
            }

            $this->reader->next();
        }

        $this->reader->iterateChildren(
            function () {
                self::fail('The child nodes should not have been iterated.');
            }
        );
    }

    /**
     * Verify that the text content of a node is returned.
     */
    public function testReadTheTextContentOfANode()
    {
        self::assertEquals(
            <<<TEXT

  alpha
  beta
  gamma
  

TEXT
,
            $this->reader->readTextContent(),
            'The expected text content was not returned.'
        );
    }

    /**
     * Verify that the value of a node is returned.
     */
    public function testReadTheValueOfANode()
    {
        self::assertEquals(
            <<<TEXT

  alpha
  
  gamma
  

TEXT
,
            $this->reader->readValue(),
            'The expected value was not returned.'

        );
    }

    /**
     * Verify that an exception is thrown if the reader is not positioned at an element.
     */
    public function testThrowAnExceptionIfTheReaderIsNotPositionedAtAnElement()
    {
        $this->reader->next();

        $this->expectException(InvalidNodePositionException::class);

        $this->reader->iterateChildren(
            function () {
            }
        );
    }

    /**
     * Verify that an exception is thrown if the reader is not positioned at the element start tag.
     */
    public function testThrowAnExceptionIfNotIteratingChildrenStartingAtTheElementStartTag()
    {
        while ($this->reader->valid()) {
            $node = $this->reader->current();

            if ($node->isElement()
                && $node->isEnd()
                && ('root' === $node->getLocalName())) {
                break;
            }

            $this->reader->next();
        }

        $this->expectException(InvalidNodePositionException::class);

        $this->reader->iterateChildren(
            function () {
            }
        );
    }

    /**
     * Creates a new advanced file reader and temporary test XML file.
     */
    protected function setUp()
    {
        $this->file = temp_file();

        file_put_contents(
            $this->file,
            <<<XML
<root>
  alpha
  <child>beta</child>
  gamma
  <child/>
</root>
XML
        );

        $this->reader = new AdvancedFileReader($this->file);
        $this->reader->rewind();
    }

    /**
     * Deletes the temporary test XML file.
     */
    protected function tearDown()
    {
        $this->reader = null;

        remove($this->file);
    }
}
