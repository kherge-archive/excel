<?php

namespace KHerGe\Excel\Database;

use KHerGe\Excel\Database;
use KHerGe\Excel\Reader\SharedStringsReader;

/**
 * Manages the table of shared strings for the database.
 *
 * This class will manage the table of shared strings for the workbook
 * database. When instantiated, the new instance will create the schema for
 * the shared strings table using the given database manager.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class SharedStringsTable
{
    /**
     * The statement to insert a new string.
     *
     * @var string
     */
    const INSERT = <<<SQL
INSERT INTO strings ("index", string) VALUES (:index, :string);
SQL;

    /**
     * The shared strings table schema.
     *
     * @var string
     */
    const SCHEMA = <<<SQL
CREATE TABLE strings (
    "index" INTEGER PRIMARY KEY,
    string  TEXT    NOT NULL
);
SQL;

    /**
     * The database manager.
     *
     * @var Database
     */
    private $database;

    /**
     * Initializes the new shared strings table manager.
     *
     * ```php
     * $table = new SharedStringsTable($database);
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
     * Imports the shared strings into the table.
     *
     * ```php
     * $reader = new SharedStringsReader('/path/to/sharedStrings.xml');
     *
     * $table->import($reader);
     * ```
     *
     * @param SharedStringsReader $reader The shared strings reader.
     */
    public function import(SharedStringsReader $reader)
    {
        $this->database->transactional(
            function (Database $database) use ($reader) {
                $insert = $database->prepare(self::INSERT);

                foreach ($reader as $index => $string) {
                    $insert->execute(
                        [
                            'index' => $index,
                            'string' => $string
                        ]
                    );
                }

                $database->release($insert);
            }
        );
    }

    /**
     * Creates the new schema for the shared strings table.
     */
    private function createSchema()
    {
        $this->database->release($this->database->execute(self::SCHEMA));
    }
}
