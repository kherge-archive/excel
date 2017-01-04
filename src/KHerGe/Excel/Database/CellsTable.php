<?php

namespace KHerGe\Excel\Database;

use Generator;
use KHerGe\Excel\Database;
use KHerGe\Excel\Reader\Worksheet\CellInfo;
use KHerGe\Excel\Reader\WorksheetReader;

/**
 * Manages the table of cell values for the database.
 *
 * This class will manage the table of cell values for the workbook database.
 * When instantiated, the new instance will create the schema for the cell
 * values table using the given database manager.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class CellsTable
{
    /**
     * The statement to check if a specific cell exists.
     *
     * @var integer
     */
    const CHECK_CELL = <<<SQL
SELECT COUNT(*)
  FROM cells
 WHERE worksheet = :worksheet
   AND "column" = :column
   AND "row" = :row;
SQL;

    /**
     * The statement to count the number of columns in a worksheet.
     *
     * @var string
     */
    const COUNT_COLUMNS = <<<SQL
SELECT MAX("column") FROM cells WHERE worksheet = :worksheet
SQL;

    /**
     * The statement to count the number of rows in a worksheet.
     *
     * @var string
     */
    const COUNT_ROWS = <<<SQL
SELECT MAX("row") FROM cells WHERE worksheet = :worksheet
SQL;

    /**
     * The statement to insert a new cell records.
     *
     * @var string
     */
    const INSERT = <<<SQL
INSERT INTO cells
            ( worksheet, "column", "row", type,  style,  value,  shared)
     VALUES (:worksheet, :column,  :row, :type, :style, :value, :shared)
SQL;

    /**
     * The cell values table schema.
     *
     * @var string
     */
    const SCHEMA = <<<SQL
CREATE TABLE cells (
    worksheet INTEGER NOT NULL,
    "column"  INTEGER NOT NULL,
    "row"     INTEGER NOT NULL,
    type      TEXT,
    style     INTEGER,
    value     TEXT,
    shared    INTEGER,

    FOREIGN KEY (worksheet) REFERENCES worksheets("index"),
    FOREIGN KEY (style)     REFERENCES styles(id),
    FOREIGN KEY (shared)    REFERENCES strings("index"),

    PRIMARY KEY (worksheet, "column", "row")
);
SQL;

    /**
     * The statement to select a cell from the table.
     *
     * @var string
     */
    const SELECT = <<<SQL
   SELECT cells."column" AS "column",
          cells."row"    AS "row",
          cells.type     AS type,
          cells.value    AS value,
          strings.string AS shared,
          formats.format AS format
     FROM cells
LEFT JOIN strings ON strings."index" = cells.shared
LEFT JOIN styles  ON styles.id = cells.style
LEFT JOIN formats on formats.id = styles.numberFormat
    WHERE cells.worksheet = :worksheet
      AND cells."column" = :column
      AND cells."row" = :row;
SQL;

    /**
     * The statement to select all cells from the table for a worksheet.
     *
     * @var string
     */
    const SELECT_ALL = <<<SQL
   SELECT cells."column" AS "column",
          cells."row"    AS "row",
          cells.type     AS type,
          cells.value    AS value,
          strings.string AS shared,
          formats.format AS format
     FROM cells
LEFT JOIN strings ON strings."index" = cells.shared
LEFT JOIN styles  ON styles.id = cells.style
LEFT JOIN formats on formats.id = styles.numberFormat
    WHERE cells.worksheet = :worksheet
 ORDER BY cells."row" ASC,
          cells."column" ASC;
SQL;

    /**
     * The statement to select all cells for a column in a worksheet.
     *
     * @var string
     */
    const SELECT_COLUMN = <<<SQL
   SELECT cells."column" AS "column",
          cells."row"    AS "row",
          cells.type     AS type,
          cells.value    AS value,
          strings.string AS shared,
          formats.format AS format
     FROM cells
LEFT JOIN strings ON strings."index" = cells.shared
LEFT JOIN styles  ON styles.id = cells.style
LEFT JOIN formats on formats.id = styles.numberFormat
    WHERE cells.worksheet = :worksheet
      AND cells."column" = :column
 ORDER BY cells."row" ASC,
          cells."column" ASC;
SQL;

    /**
     * The statement to select all cells for a row in a worksheet.
     *
     * @var string
     */
    const SELECT_ROW = <<<SQL
   SELECT cells."column" AS "column",
          cells."row"    AS "row",
          cells.type     AS type,
          cells.value    AS value,
          strings.string AS shared,
          formats.format AS format
     FROM cells
LEFT JOIN strings ON strings."index" = cells.shared
LEFT JOIN styles  ON styles.id = cells.style
LEFT JOIN formats on formats.id = styles.numberFormat
    WHERE cells.worksheet = :worksheet
      AND cells."row" = :row
 ORDER BY cells."row" ASC,
          cells."column" ASC;
SQL;

    /**
     * The database manager.
     *
     * @var Database
     */
    private $database;

    /**
     * Initializes the new cell values table manager.
     *
     * @param Database $database The database manager.
     */
    public function __construct(Database $database)
    {
        $this->database = $database;

        $this->createSchema();
    }

    /**
     * Returns the total number of columns for a worksheet.
     *
     * ```php
     * $count = $table->countColumns(123);
     * ```
     *
     * @param integer $index The index of the worksheet.
     *
     * @return integer|null The number of columns.
     */
    public function countColumns($index)
    {
        return $this->database->column(
            self::COUNT_COLUMNS,
            ['worksheet' => $index]
        );
    }

    /**
     * Returns the total number of rows for a worksheet.
     *
     * ```php
     * $count = $table->countRows(123);
     * ```
     *
     * @param integer $index The index of the worksheet.
     *
     * @return integer|null The number of rows.
     */
    public function countRows($index)
    {
        return $this->database->column(
            self::COUNT_ROWS,
            ['worksheet' => $index]
        );
    }

    /**
     * Returns the data for a cell.
     *
     * ```php
     * $cell = $table->getCell(123, 'A', 1);
     * ```
     *
     * @param integer $worksheet The index of the worksheet.
     * @param string  $column    The name of the column.
     * @param integer $row       The number of the row.
     *
     * @return array|null The cell data.
     */
    public function getCell($worksheet, $column, $row)
    {
        $cell = $this->database->row(
            self::SELECT,
            [
                'worksheet' => $worksheet,
                'column' => $this->toIndex($column),
                'row' => $row
            ]
        );

        if (null !== $cell) {
            $cell['column'] = $this->toName($cell['column']);
        }

        return $cell;
    }

    /**
     * Returns the data for an entire row.
     *
     * ```php
     * $row = $table->getRow(123, 456);
     *
     * foreach ($row as $cell) {
     *     // ...
     * }
     * ```
     *
     * @param integer $worksheet The index of the worksheet.
     * @param integer $row       The number of the row.
     *
     * @return array[]|null The cell values.
     */
    public function getRow($worksheet, $row)
    {
        $row = $this->database->all(
            self::SELECT_ROW,
            [
                'worksheet' => $worksheet,
                'row' => $row
            ]
        );

        foreach ($row as $i => $cell) {
            $row[$i]['column'] = $this->toName($cell['column']);
        }

        return empty($row) ? null : $row;
    }

    /**
     * Checks if a cell exists in a worksheet.
     *
     * ```php
     * if ($table->hasCell(1, 'B', 3)) {
     *     // The cell exists.
     * }
     * ```
     *
     * @param integer $worksheet The index of the worksheet.
     * @param string  $column    The name of the column.
     * @param integer $row       The number of the row.
     *
     * @return boolean Returns `true` if the cell exists or `false` if not.
     */
    public function hasCell($worksheet, $column, $row)
    {
        $result = $this->database->column(
            self::CHECK_CELL,
            [
                'worksheet' => $worksheet,
                'column' => $this->toIndex($column),
                'row' => $row
            ]
        );

        return ('1' === $result);
    }

    /**
     * Checks if a column exists in a worksheet.
     *
     * ```php
     * if ($table->hasColumn(1, 'A')) {
     *     // The column exists.
     * }
     * ```
     *
     * @param integer $worksheet The index of the worksheet.
     * @param string  $column    The name of the column.
     *
     * @return boolean Returns `true` if the column exists or `false` if not.
     */
    public function hasColumn($worksheet, $column)
    {
        $count = $this->countColumns($worksheet);

        if (null !== $count) {
            $column = $this->toIndex($column);

            return (($column > 0) && ($column <= $count));
        }

        return false;
    }

    /**
     * Checks if a row exists in a worksheet.
     *
     * ```php
     * if ($table->hasRow(1, 1)) {
     *     // The row exists.
     * }
     * ```
     *
     * @param integer $worksheet The index of the worksheet.
     * @param integer $row       The number of the row.
     *
     * @return boolean Returns `true` if the row exists or `false` if not.
     */
    public function hasRow($worksheet, $row)
    {
        $count = $this->countRows($worksheet);

        if (null !== $count) {
            return (($row > 0) && ($row <= $count));
        }

        return false;
    }

    /**
     * Imports the cells from a worksheet.
     *
     * This method will ...
     *
     * ```php
     * $reader = new WorksheetReader('/path/to/worksheet.xml');
     *
     * $table->import($reader);
     * ```
     *
     * @param WorksheetReader $reader The worksheet reader.
     * @param integer         $index  The index of the worksheet.
     */
    public function import(WorksheetReader $reader, $index)
    {
        $this->database->transactional(
            function (Database $database) use ($index, $reader) {
                foreach ($reader as $data) {
                    if ($data instanceof CellInfo) {
                        $shared = ('s' === $data->getType());

                        $database->release(
                            $database->execute(
                                self::INSERT,
                                [
                                    'worksheet' => $index,
                                    'column' => $this->toIndex($data->getColumn()),
                                    'row' => $data->getRow(),
                                    'type' => $data->getType(),
                                    'style' => $data->getStyleId(),
                                    'value' => $shared
                                        ? null
                                        : $data->getRawValue(),
                                    'shared' => $shared
                                        ? $data->getRawValue()
                                        : null
                                ]
                            )
                        );
                    }
                }
            }
        );
    }

    /**
     * Iterates through each value in a column for a worksheet and returns the
     * cell values.
     *
     * ```php
     * foreach ($table->iterateColumn(1, 'A') as $cell) {
     *     // ...
     * }
     * ```
     *
     * @param integer $worksheet The index of the worksheet.
     * @param string  $column    The name of the column.
     *
     * @return array[]|Generator The cell values.
     */
    public function iterateColumn($worksheet, $column)
    {
        $cells = $this->database->iterate(
            self::SELECT_COLUMN,
            [
                'worksheet' => $worksheet,
                'column' => $this->toIndex($column)
            ]
        );

        foreach ($cells as $cell) {
            $cell['column'] = $this->toName($cell['column']);

            yield $cell;
        }
    }

    /**
     * Iterates through each row in a worksheet and returns the cell values.
     *
     * ```php
     * foreach ($table->iterateRows(1) as $cells) {
     *     // ...
     * }
     * ```
     *
     * @param integer $worksheet The index of the worksheet.
     *
     * @return array[]|Generator The cell values.
     */
    public function iterateRows($worksheet)
    {
        $row = [];
        $number = null;
        $cells = $this->database->iterate(
            self::SELECT_ALL,
            ['worksheet' => $worksheet]
        );

        foreach ($cells as $cell) {
            $cell['column'] = $this->toName($cell['column']);

            if ($cell['row'] !== $number) {
                if (null !== $number) {
                    yield $row;
                }

                $number = $cell['row'];
                $row = [];
            }

            $row[] = $cell;
        }

        yield $row;
    }

    /**
     * Creates the new schema for the cell values table.
     */
    private function createSchema()
    {
        $this->database->release($this->database->execute(self::SCHEMA));
    }

    /**
     * Converts a column name into an index.
     *
     * @param string $name The name of the column.
     *
     * @return integer The index.
     */
    private function toIndex($name)
    {
        $letters = array_reverse(str_split(strtoupper($name)));
        $index = 0;
        $power = 1;

        foreach ($letters as $letter) {
            $letter = ord($letter) - 64;
            $index += $letter * $power;
            $power *= 26;
        }

        return $index;
    }

    /**
     * Converts a column index into a name.
     *
     * @param integer $index The index of the column.
     *
     * @return string The name.
     */
    private function toName($index)
    {
        $letter = '';

        while ($index > 0) {
            $value = ($index - 1) % 26;
            $letter = chr($value + 65) . $letter;
            $index = ($index - $value - 1) / 26;
        }

        return $letter;
    }
}
