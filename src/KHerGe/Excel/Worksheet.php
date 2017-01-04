<?php

namespace KHerGe\Excel;

use Generator;
use KHerGe\Excel\Database\CellsTable;
use KHerGe\Excel\Exception\Worksheet\NoSuchCellException;
use KHerGe\Excel\Exception\Worksheet\NoSuchColumnException;
use KHerGe\Excel\Exception\Worksheet\NoSuchRowException;

/**
 * Manages access to the contents of a worksheet in an Excel workbook file.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Worksheet
{
    /**
     * The cell values table manager.
     *
     * @var Database
     */
    private $cells;

    /**
     * The value decoder.
     *
     * @var Decoder
     */
    private $decoder;

    /**
     * The index of the worksheet.
     *
     * @var integer
     */
    private $index;

    /**
     * The name of the worksheet.
     *
     * @var string
     */
    private $name;

    /**
     * Initializes the new worksheet manager.
     *
     * ```php
     * $worksheet = new Worksheet($database, '/path/to/worksheet.xml');
     * ```
     *
     * @param CellsTable $table The cells value table manager.
     * @param integer    $index The index of the worksheet.
     * @param string     $name  The name of the worksheet.
     */
    public function __construct(CellsTable $table, $index, $name)
    {
        $this->cells = $table;
        $this->decoder = new Decoder();
        $this->index = $index;
        $this->name = $name;
    }

    /**
     * Returns the number of columns in the worksheet.
     *
     * ```php
     * $count = $worksheet->countColumns();
     * ```
     *
     * @return integer The number of columns in the worksheet.
     */
    public function countColumns()
    {
        return $this->cells->countColumns($this->index);
    }

    /**
     * Returns the number of rows in the worksheet.
     *
     * ```php
     * $count = $worksheet->countRows();
     * ```
     *
     * @return integer The number of rows.
     */
    public function countRows()
    {
        return $this->cells->countRows($this->index);
    }

    /**
     * Returns the value of a specific cell.
     *
     * ```php
     * $value = $worksheet->getCell('A', 1);
     * ```
     *
     * @param string  $column The name of the column.
     * @param integer $row    The number of the row.
     *
     * @return mixed The value of the cell.
     *
     * @throws NoSuchCellException If the cell does not exist.
     */
    public function getCell($column, $row)
    {
        $cell = $this->cells->getCell($this->index, $column, $row);

        if (null === $cell) {
            throw new NoSuchCellException(
                'The cell for column "%s" row "%s" in the worksheet "%s" (index: %s) does not exist.',
                $column,
                $row,
                $this->name,
                $this->index
            );
        }

        return $this->decoder->decode($cell);
    }

    /**
     * Returns the index of the worksheet.
     *
     * @return integer The index.
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Returns the name of the worksheet.
     *
     * @return string The name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns all of the column values for a specific row.
     *
     * ```php
     * $values = $worksheet->getRow(1);
     *
     * echo $values['A'], "\n";
     * ```
     *
     * @param integer $row The number of the row.
     *
     * @return mixed[] The row values.
     *
     * @throws NoSuchRowException If the row does not exist.
     */
    public function getRow($row)
    {
        $rows = [];
        $cells = $this->cells->getRow($this->index, $row);

        if (null === $cells) {
            throw new NoSuchRowException(
                'The row "%s" does not exist in the worksheet "%s" (index: %s).',
                $row,
                $this->name,
                $this->index
            );
        }

        foreach ($cells as $cell) {
            $rows[$cell['column']] = $this->decoder->decode($cell);
        }

        return $rows;
    }

    /**
     * Checks if a cell exists in the worksheet.
     *
     * ```php
     * if ($worksheet->hasCell('A', 1)) {
     *     // The cell exists.
     * }
     * ```
     * @param string  $column The name of the column.
     * @param integer $row    The number of the row.
     *
     * @return boolean Returns `true` if the cell exists or `false` if not.
     */
    public function hasCell($column, $row)
    {
        return $this->cells->hasCell($this->index, $column, $row);
    }

    /**
     * Checks if a column exists in the worksheet.
     *
     * ```php
     * if ($worksheet->hasColumn('A')) {
     *     // The column exists.
     * }
     * ```
     * @param string $column The name of the column.
     *
     * @return boolean Returns `true` if the column exists or `false` if not.
     */
    public function hasColumn($column)
    {
        return $this->cells->hasColumn($this->index, $column);
    }

    /**
     * Checks if a row exists in the worksheet.
     *
     * ```php
     * if ($worksheet->hasRow(1)) {
     *     // The row exists.
     * }
     * ```
     * @param integer $row The number of the row.
     *
     * @return boolean Returns `true` if the row exists or `false` if not.
     */
    public function hasRow($row)
    {
        return $this->cells->hasRow($this->index, $row);
    }

    /**
     * Iterates through a column in the worksheet.
     *
     * This method will yield each value for a column in the worksheet in the
     * order that they are found. Each iteration will yield the number of the
     * row as the key and the value as the value.
     *
     * ```php
     * foreach ($worksheet->iterateColumn('A') as $row => $value) {
     *     echo 'A', $row, ' = ', $value, "\n";
     * }
     * ```
     *
     * @param string $column The name of the column.
     *
     * @return Generator|mixed[] The column values.
     *
     * @throws NoSuchColumnException If the column does not exist.
     */
    public function iterateColumn($column)
    {
        foreach ($this->cells->iterateColumn($this->index, $column) as $cell) {
            yield intval($cell['row']) => $this->decoder->decode($cell);
        }
    }

    /**
     * Iterates through each row in the worksheet.
     *
     * This method will yield each row of values in the worksheet in the order
     * that they are found. Each iteration will yield the number of the row as
     * the key, and an array of values as the value.
     *
     * ```php
     * foreach ($worksheet->iterateRows() as $row => $columns) {
     *     echo 'A', $row, ' = ', $columns['A'], "\n";
     * }
     * ```
     *
     * The values array may look something like the following:
     *
     * ```php
     * $columns = [
     *     'A' => 'Value in column A.',
     *     'B' => 123,
     *     'C' => new DateTime('2017),
     *     'D' => null
     * ];
     * ```
     *
     * @return Generator|mixed[] The row values.
     */
    public function iterateRows()
    {
        foreach ($this->cells->iterateRows($this->index) as $row) {
            $number = intval($row[0]['row']);

            foreach ($row as $i => $cell) {
                unset($row[$i]);

                $row[$cell['column']] = $this->decoder->decode($cell);
            }

            yield $number => $row;
        }
    }
}
