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
     * The default number formats.
     *
     * @var string[]
     */
    private static $defaultFormats = [
        0 => 'General',
        1 => '0',
        2 => '0.00',
        3 => '#,##0',
        4 => '#,##0.00',
        9 => '0%',
        10 => '0.00%',
        11 => '0.00E+00',
        12 => '# ?/?',
        13 => '# ??/??',
        14 => 'mm-dd-yy',
        15 => 'd-mmm-yy',
        16 => 'd-mmm',
        17 => 'mmm-yy',
        18 => 'h:mm AM/PM',
        19 => 'h:mm:ss AM/PM',
        20 => 'h:mm',
        21 => 'h:mm:ss',
        22 => 'm/d/yy h:mm',
        37 => '#,##0 ;(#,##0)',
        38 => '#,##0 ;[Red](#,##0)',
        39 => '#,##0.00;(#,##0.00)',
        40 => '#,##0.00;[Red](#,##0.00)',
        45 => 'mm:ss',
        46 => '[h]:mm:ss',
        47 => 'mmss.0',
        48 => '##0.0E+0',
        49 => '@'
    ];

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
                foreach ($reader as $data) {
                    if ($data instanceof NumberFormatInfo) {
                        $database->release(
                            $database->execute(
                                self::INSERT,
                                [
                                    'id' => $data->getId(),
                                    'format' => $data->getFormatCode()
                                ]
                            )
                        );
                    }
                }
            }
        );
    }

    /**
     * Creates the new schema for the number formats table.
     */
    private function createSchema()
    {
        $this->database->release($this->database->execute(self::SCHEMA));

        $insert = $this->database->prepare(self::INSERT);

        foreach (self::$defaultFormats as $id => $format) {
            $insert->execute(
                [
                    'id' => $id,
                    'format' => $format
                ]
            );
        }

        $this->database->release($insert);
    }
}
