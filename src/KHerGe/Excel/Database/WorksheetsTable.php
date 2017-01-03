<?php

namespace KHerGe\Excel\Database;

use Countable;
use KHerGe\Excel\Database;
use KHerGe\Excel\Reader\Workbook\WorksheetInfo;
use KHerGe\Excel\Reader\WorkbookReader;

/**
 * Manages the table of worksheets for the database.
 *
 * This class will create a table of worksheets and manage its data. When a
 * new instance of this class is created, the table is automatically created
 * even if it already exists. You should only maintain a single instance of
 * this class per workbook.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class WorksheetsTable implements Countable
{
    /**
     * The statement to select all records in the table.
     *
     * @var string
     */
    const ALL = <<<SQL
SELECT "index", name FROM worksheets;
SQL;

    /**
     * The statement to count the number of worksheets.
     *
     * @var string
     */
    const COUNT = <<<SQL
SELECT COUNT(*) FROM worksheets;
SQL;

    /**
     * The statement to count the number of worksheets by index.
     *
     * @var string
     */
    const COUNT_INDEX = <<<SQL
SELECT COUNT(*) FROM worksheets WHERE "index" = :index;
SQL;

    /**
     * The statement to count the number of worksheets by name.
     *
     * @var string
     */
    const COUNT_NAME = <<<SQL
SELECT COUNT(*) FROM worksheets WHERE name = :name;
SQL;

    /**
     * The statement to insert a new worksheet.
     *
     * @var string
     */
    const INSERT = <<<SQL
INSERT INTO worksheets ("index", name) VALUES (:index, :name);
SQL;

    /**
     * The worksheets table schema.
     *
     * @var string
     */
    const SCHEMA = <<<SQL
CREATE TABLE worksheets (
    "index" INTEGER PRIMARY KEY,
    name    TEXT UNIQUE NOT NULL
);
SQL;

    /**
     * The statement to select a worksheet index by name.
     *
     * @var string
     */
    const SELECT_INDEX = <<<SQL
SELECT "index" FROM worksheets WHERE name = :name;
SQL;

    /**
     * The statement to select a worksheet name by index.
     *
     * @var string
     */
    const SELECT_NAME = <<<SQL
SELECT name FROM worksheets WHERE "index" = :index;
SQL;

    /**
     * The database manager.
     *
     * @var Database
     */
    private $database;

    /**
     * Initializes the new worksheets table manager.
     *
     * ```php
     * $table = new WorksheetsTable($database);
     * ```
     *
     * @param Database $database The database manager.
     */
    public function __construct(Database $database)
    {
        $this->database = $database;

        $this->createSchema();
    }

    /**
     * Returns the number of worksheets in the table.
     *
     * ```php
     * $count = count($table);
     * ```
     *
     * @return integer The number of worksheets.
     */
    public function count()
    {
        return (int) $this->database->column(self::COUNT);
    }

    /**
     * Returns the index of a worksheet by using its name.
     *
     * ```php
     * $index = $table->getIndexByName('Example');
     * ```
     *
     * @param string $name The name of the worksheet.
     *
     * @return integer|null The index of the worksheet.
     */
    public function getIndexByName($name)
    {
        $index = $this->database->column(
            self::SELECT_INDEX,
            ['name' => $name]
        );

        return (null === $index) ? null : (int) $index;
    }

    /**
     * Returns a list of all worksheets in the table.
     *
     * This method will return an array that lists all of the worksheets in
     * the table. The array key is the index of the worksheet and the value
     * is the name of the worksheet.
     *
     * ```php
     * foreach ($table->getList() as $index => $name) {
     *     // ...
     * }
     * ```
     *
     * @return string[] The list of worksheets.
     */
    public function getList()
    {
        return $this->database->all(self::ALL, [], 'index', 'name');
    }

    /**
     * Returns the name of a worksheet by using its index.
     *
     * ```php
     * $name = $table->getNameByIndex(123);
     * ```
     *
     * @param integer $index The index of the worksheet.
     *
     * @return null|string The name of the worksheet.
     */
    public function getNameByIndex($index)
    {
        return $this->database->column(
            self::SELECT_NAME,
            ['index' => $index]
        );
    }

    /**
     * Checks if a worksheet with an index exists.
     *
     * ```php
     * if ($table->hasIndex(123)) {
     *     // The worksheet exists.
     * }
     * ```
     *
     * @param integer $index The index of the worksheet.
     *
     * @return boolean Returns `true` if it exists or `false` if not.
     */
    public function hasIndex($index)
    {
        $count = $this->database->column(
            self::COUNT_INDEX,
            ['index' => $index]
        );

        return ('1' === $count);
    }

    /**
     * Checks if a worksheet with a name exists.
     *
     * ```php
     * if ($table->hasName('Example')) {
     *     // The worksheet exists.
     * }
     * ```
     *
     * @param string $name The name of the worksheet.
     *
     * @return boolean Returns `true` if it exists or `false` if not.
     */
    public function hasName($name)
    {
        $count = $this->database->column(
            self::COUNT_NAME,
            ['name' => $name]
        );

        return ('1' === $count);
    }

    /**
     * Imports the list of worksheets into the table.
     *
     * ```php
     * $reader = new WorkbookReader('/path/to/workbook.xml');
     *
     * $table->import($reader);
     * ```
     *
     * @param WorkbookReader $reader The workbook reader.
     */
    public function import(WorkbookReader $reader)
    {
        $this->database->transactional(
            function (Database $database) use ($reader) {
                $insert = $database->prepare(self::INSERT);

                foreach ($reader as $data) {
                    if ($data instanceof WorksheetInfo) {
                        $insert->execute(
                            [
                                'index' => $data->getIndex(),
                                'name' => $data->getName()
                            ]
                        );
                    }
                }

                $database->release($insert);
            }
        );
    }

    /**
     * Creates the new schema for the worksheets table.
     */
    private function createSchema()
    {
        $this->database->release($this->database->execute(self::SCHEMA));
    }
}
