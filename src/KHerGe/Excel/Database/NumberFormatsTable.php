<?php

namespace KHerGe\Excel\Database;

use KHerGe\Excel\Database;
use KHerGe\Excel\Reader\Styles\NumberFormatInfo;
use KHerGe\Excel\Reader\StylesReader;

/**
 * Manages the table of number formats for the database.
 *
 * This class will manage the table of number formats for the workbook
 * database. When instantiated, the new instance will create the schema
 * for the number formats using the given database manager.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class NumberFormatsTable
{
    /**
     * The statement to insert a new number format.
     *
     * @var string
     */
    const INSERT = <<<SQL
INSERT INTO formats (id, format) VALUES (:id, :format);
SQL;

    /**
     * The number formats table schema.
     *
     * @var string
     */
    const SCHEMA = <<<SQL
CREATE TABLE formats (
    id     INTEGER PRIMARY KEY,
    format TEXT NOT NULL
);
SQL;

    /**
     * The database manager.
     *
     * @var Database
     */
    private $database;

    /**
     * Initializes the new number formats table manager.
     *
     * @param Database $database The database manager.
     */
    public function __construct(Database $database)
    {
        $this->database = $database;

        $this->createSchema();
    }

    /**
     * Imports the number formats into the table.
     *
     * ```php
     * $reader = new StylesReader('/path/to/styles.xml');
     *
     * $table->import($reader);
     * ```
     *
     * @param StylesReader $reader The styles reader.
     */
    public function import(StylesReader $reader)
    {
        $this->database->transactional(
            function (Database $database) use ($reader) {
                $insert = $database->prepare(self::INSERT);

                foreach ($reader as $data) {
                    if ($data instanceof NumberFormatInfo) {
                        $insert->execute(
                            [
                                'id' => $data->getId(),
                                'format' => $data->getFormatCode()
                            ]
                        );
                    }
                }

                $database->release($insert);
            }
        );
    }

    /**
     * Creates the new schema for the number formats table.
     */
    private function createSchema()
    {
        $this->database->release($this->database->execute(self::SCHEMA));
    }
}
