<?php

namespace KHerGe\Excel\Database;

use KHerGe\Excel\Database;
use KHerGe\Excel\Reader\Styles\CellStyleInfo;
use KHerGe\Excel\Reader\StylesReader;

/**
 * Manages the table of cell styles for the database.
 *
 * This class will manage the table of cell styles for the workbook database.
 * When instantiated, the new instance will create the schema for the cell
 * styles using the given database manager.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class StylesTable
{
    /**
     * The statement to insert a new number format.
     *
     * @var string
     */
    const INSERT = <<<SQL
INSERT INTO styles
            ( id,  isNumber,  numberFormat)
     VALUES (:id, :isNumber, :numberFormat);
SQL;

    /**
     * The number formats table schema.
     *
     * @var string
     */
    const SCHEMA = <<<SQL
CREATE TABLE styles (
    id           INTEGER PRIMARY KEY,
    isNumber     INTEGER NOT NULL,
    numberFormat INTEGER,
    
    FOREIGN KEY (numberFormat) REFERENCES formats(id)
);
SQL;

    /**
     * The database manager.
     *
     * @var Database
     */
    private $database;

    /**
     * Initializes the new cell styles table manager.
     *
     * @param Database $database The database manager.
     */
    public function __construct(Database $database)
    {
        $this->database = $database;

        $this->createSchema();
    }

    /**
     * Imports the cell styles into the table.
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
                    if ($data instanceof CellStyleInfo) {
                        $insert->execute(
                            [
                                'id' => $data->getId(),
                                'isNumber' => $data->isNumberFormat()
                                    ? 1
                                    : 0,
                                'numberFormat' => $data->getNumberFormatId()
                            ]
                        );
                    }
                }

                $database->release($insert);
            }
        );
    }

    /**
     * Creates the new schema for the cell styles table.
     */
    private function createSchema()
    {
        $this->database->release($this->database->execute(self::SCHEMA));
    }
}
