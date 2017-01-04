<?php

namespace KHerGe\Excel;

use Generator;
use KHerGe\Excel\Database\CellsTable;
use KHerGe\Excel\Database\NumberFormatsTable;
use KHerGe\Excel\Database\SharedStringsTable;
use KHerGe\Excel\Database\StylesTable;
use KHerGe\Excel\Database\WorksheetsTable;
use KHerGe\Excel\Exception\Workbook\NoSuchWorksheetException;
use KHerGe\Excel\Reader\SharedStringsReader;
use KHerGe\Excel\Reader\StylesReader;
use KHerGe\Excel\Reader\WorkbookReader;
use KHerGe\Excel\Reader\WorksheetReader;

/**
 * Manages access to the contents of an Excel (.xlsx) workbook file.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class Workbook
{
    /**
     * The cell values table manager.
     *
     * @var CellsTable
     */
    private $cells;

    /**
     * The database manager.
     *
     * @var Database
     */
    private $database;

    /**
     * The path to the workbook file.
     *
     * @var string
     */
    private $file;

    /**
     * The number formats table manager.
     *
     * @var null|NumberFormatsTable
     */
    private $numberFormats;

    /**
     * The shared strings table manager.
     *
     * @var null|SharedStringsTable
     */
    private $sharedStrings;

    /**
     * The styles table manager.
     *
     * @var null|StylesTable
     */
    private $styles;

    /**
     * The worksheets table manager.
     *
     * @var WorksheetsTable
     */
    private $worksheets;

    /**
     * The imported worksheets.
     *
     * @var Worksheet[]
     */
    private $worksheetsImported = [];

    /**
     * Initializes the new workbook manager.
     *
     * ```php
     * $workbook = new Workbook('/path/to/workbook.xlsx');
     * ```
     *
     * @param string $file The path to the workbook file.
     */
    public function __construct($file)
    {
        $this->database = Database::create();

        $this->file = $file;

        $this->importWorksheetList();
    }

    /**
     * Returns the number of worksheets in the workbook.
     *
     * ```php
     * $count = $workbook->countWorksheets();
     * ```
     *
     * @return integer The number of worksheets.
     */
    public function countWorksheets()
    {
        return count($this->worksheets);
    }

    /**
     * Checks if a worksheet with the given index exists in the workbook.
     *
     * ```php
     * if ($workbook->hasWorksheetByIndex(123)) {
     *     // This worksheet exists.
     * }
     * ```
     *
     * @param integer $index The index of the worksheet.
     *
     * @return boolean Returns `true` if the worksheet exits or `false` if not.
     */
    public function hasWorksheetByIndex($index)
    {
        return $this->worksheets->hasIndex($index);
    }

    /**
     * Checks if a worksheet with the given name exists in the workbook.
     *
     * ```php
     * if ($workbook->hasWorksheetByName('Example')) {
     *     // The worksheet exists.
     * }
     * ```
     *
     * @param string $name The name of the worksheet.
     *
     * @return boolean Returns `true` if the worksheet exits or `false` if not.
     */
    public function hasWorksheetByName($name)
    {
        return $this->worksheets->hasName($name);
    }

    /**
     * Finds a worksheet in the workbook with the given index and returns it.
     *
     * ```php
     * $worksheet = $workbook->getWorksheetByIndex(123);
     *
     * echo $worksheet->getName(), "\n";
     * ```
     *
     * @param integer $index The index of the worksheet.
     *
     * @return Worksheet The worksheet.
     *
     * @throws NoSuchWorksheetException If the worksheet does not exist.
     */
    public function getWorksheetByIndex($index)
    {
        $name = $this->worksheets->getNameByIndex($index);

        if (null === $name) {
            throw new NoSuchWorksheetException(
                'A worksheet with the index "%s" does not exist.',
                $index
            );
        }

        return $this->createWorksheet($index, $name);
    }

    /**
     * Finds a worksheet in the workbook with the given name and returns it.
     *
     * ```php
     * $worksheet = $workbook->getWorksheetByName('Example');
     *
     * echo $worksheet->getIndex(), "\n";
     * ```
     *
     * @param string $name The name of the worksheet.
     *
     * @return Worksheet The worksheet.
     *
     * @throws NoSuchWorksheetException If the worksheet does not exist.
     */
    public function getWorksheetByName($name)
    {
        $index = $this->worksheets->getIndexByName($name);

        if (null === $index) {
            throw new NoSuchWorksheetException(
                'A worksheet with the name "%s" does not exist.',
                $name
            );
        }

        return $this->createWorksheet($index, $name);
    }

    /**
     * Iterates through each worksheet in the workbook.
     *
     * This method will yield each worksheet from the workbook in the order
     * that they are found. Each iteration will yield the index a the worksheet
     * as the key, and a `Worksheet` instance as the value.
     *
     * ```php
     * foreach ($workbook->iterateWorksheets() as $index => $worksheet) {
     *     echo 'Worksheet ', $index, ' ("', $worksheet->getName(), "\")\n";
     * }
     * ```
     *
     * @return Generator|Worksheet[] The worksheets.
     */
    public function iterateWorksheets()
    {
        foreach ($this->worksheets->getList() as $index => $name) {
            yield $index => $this->createWorksheet($index, $name);
        }
    }

    /**
     * Returns a list of worksheets in the workbook.
     *
     * This method will return an array containing the list of worksheets in
     * the workbook. The array key is the index of a worksheet and the value
     * is the name of a worksheet.
     *
     * ```php
     * foreach ($workbook->listWorksheets() as $index => $name) {
     *     echo 'Worksheet ', $index, ' ("', $name, "\")\n";
     * }
     * ```
     *
     * @return string[] The list of worksheets.
     */
    public function listWorksheets()
    {
        return $this->worksheets->getList();
    }

    /**
     * Creates a new manager for a worksheet.
     *
     * This method will import the worksheet data and create a new `Worksheet`
     * instance. If a worksheet with the given index has already been imported,
     * the previous `Worksheet` instance will be returned.
     *
     * ```php
     * $worksheet = $this->createWorksheet(123, 'Example');
     * ```
     *
     * @param integer $index The index of the worksheet.
     * @param string  $name  The name of the worksheet.
     *
     * @return Worksheet The new worksheet manager.
     */
    private function createWorksheet($index, $name)
    {
        if (!isset($this->worksheetsImported[$index])) {
            $this->importNumberFormats();
            $this->importSharedStrings();
            $this->importStyles();
            $this->initializeCellValues();

            $this->cells->import(
                new WorksheetReader(
                    sprintf(
                        'zip://%s#xl/worksheets/sheet%d.xml',
                        $this->file,
                        $index
                    )
                ),
                $index
            );

            $this->worksheetsImported[$index] = new Worksheet(
                $this->cells,
                $index,
                $name
            );
        }

        return $this->worksheetsImported[$index];
    }

    /**
     * Imports the number formats from the workbook.
     */
    private function importNumberFormats()
    {
        if (null !== $this->numberFormats) {
            return;
        }

        $this->numberFormats = new NumberFormatsTable($this->database);
        $this->numberFormats->import(
            new StylesReader(
                'zip://' . $this->file . '#xl/styles.xml'
            )
        );
    }

    /**
     * Imports the shared strings from the workbook.
     */
    private function importSharedStrings()
    {
        if (null !== $this->sharedStrings) {
            return;
        }

        $this->sharedStrings = new SharedStringsTable($this->database);
        $this->sharedStrings->import(
            new SharedStringsReader(
                'zip://' . $this->file . '#xl/sharedStrings.xml'
            )
        );
    }

    /**
     * Imports the number formats from the workbook.
     */
    private function importStyles()
    {
        if (null !== $this->styles) {
            return;
        }

        $this->styles = new StylesTable($this->database);
        $this->styles->import(
            new StylesReader(
                'zip://' . $this->file . '#xl/styles.xml'
            )
        );
    }

    /**
     * Imports the list of worksheets from the workbook.
     */
    private function importWorksheetList()
    {
        $this->worksheets = new WorksheetsTable($this->database);
        $this->worksheets->import(
            new WorkbookReader(
                'zip://' . $this->file . '#xl/workbook.xml'
            )
        );
    }

    /**
     * Initializes the cell values table manager.
     */
    private function initializeCellValues()
    {
        if (null === $this->cells) {
            $this->cells = new CellsTable($this->database);
        }
    }
}
