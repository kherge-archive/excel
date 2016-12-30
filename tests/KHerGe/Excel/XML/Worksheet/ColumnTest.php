<?php

namespace Test\KHerGe\Excel\XML\Worksheet;

use KHerGe\Excel\XML\Worksheet\Column;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Verifies that the column representation functions as intended.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 *
 * @covers \KHerGe\Excel\XML\Worksheet\Column
 */
class ColumnTest extends TestCase
{
    /**
     * The column representation.
     *
     * @var Column
     */
    private $column;

    /**
     * The unique identifier for the cell style.
     *
     * @var integer
     */
    private $styleId = 123;

    /**
     * Verify that the unique identifier for the cell style is returned.
     */
    public function testGetTheUniqueIdentifierForTheCellStyle()
    {
        self::assertEquals(
            $this->styleId,
            $this->column->getStyleId(),
            'The unique identifier for the cell style was not returned.'
        );
    }

    /**
     * Creates a new column representation.
     */
    protected function setUp()
    {
        $this->column = new Column($this->styleId);
    }
}
