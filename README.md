[![Build Status](https://travis-ci.org/kherge-php/excel.svg?branch=master)](https://travis-ci.org/kherge-php/excel)
[![Packagist](https://img.shields.io/packagist/v/kherge/excel.svg)]()
[![Packagist Pre Release](https://img.shields.io/packagist/vpre/kherge/excel.svg)]()

Excel
=====

Reads very large Excel (.xlsx) workbook files.

Usage
-----

```php
<?php

use KHerGe\Excel\Workbook;

// Open a workbook file.
$workbook = new Workbook('/path/to/workbook.xlsx');

// Show list of worksheets.
print_r($workbook->listWorksheets());

// Get a specific worksheet by index.
$worksheet = $workbook->getWorksheetByIndex(123);

// Get a specific worksheet by name.
$worksheet = $workbook->getWorksheetByName('Example');

// Iterate through all worksheets.
foreach ($workbook->iterateWorksheets() as $index => $worksheet) {
    // ...
}

// Iterate through all rows in a worksheet.
foreach ($worksheet->iterateRows() as $row => $values) {
    foreach ($values as $column => $value) {
        echo $column, $row, ' = ', $value, "\n"; // A1 = example
    }
}

// Get a specific cell.
$value = $worksheet->getCell('C', 3);

// Get a specific row.
$row = $worksheet->getRow(123);
```

Performance
-----------

Metrics are TBD.

All I know for sure is that this library will read any size spreadsheet while
using less than 2 MiB of RAM. The process of reading the spreadsheet is very
CPU intensive, however.

Requirements
------------

- PHP 5.6+
    - pdo_sqlite
    - zip

Installation
------------

    composer require kherge/excel

Documentation
-------------

There are only two classes you use directly.

### Workbook

The `KHerGe\Excel\Workbook` class provides access to the worksheets in a workbook.

You instantiate the class by providing it the path of the workbook file as its
only argument.

```php
<?php

$workbook = new KHerGe\Excel\Workbook('/path/to/example.xlsx');
```

| Method                | Signature                     | Description |
|:----------------------|:------------------------------|:------------|
| `countWorksheets`     | `() -> int`                   | Counts the number of worksheets in the workbook. |
| `hasWorksheetByIndex` | `(int) -> bool`               | Checks if a worksheet with a given index exists in the workbook. |
| `hasWorksheetByName`  | `(str) -> bool`               | Checks if a worksheet with a given name exists in the workbook. |
| `getWorksheetByIndex` | `(int) -> Worksheet`          | Retrieves a worksheet by its index. |
| `getWorksheetByName`  | `(str) -> Worksheet`          | Retrieves a worksheet by its name. |
| `iterateWorksheets`   | `() -> yield<int, Worksheet>` | Yields each worksheet in the workbook. The key is the index of the worksheet and the value is the `Worksheet` instance. |
| `listWorksheets`      | `() -> map<int, str>`         | Gets a list of worksheets in the workbook. The key is the index of the worksheet and the value is the name. |

### Worksheet

The `KHerGe\Excel\Worksheet` class provides access to the contents of an
individual worksheet in the workbook. Instances of this class are only
returned by the `Workbook` class using one of the `getWorksheet*` methods.

| Method          | Signature                           | Description |
|:----------------|:------------------------------------|:------------|
| `countColumns`  | `() -> int`                         | Counts the number of columns in the worksheet. |
| `countRows`     | `() -> int`                         | Counts the number of rows in the worksheet. |
| `getCell`       | `(str, int) -> mixed`               | Gets the value for a specific cell. The first argument is the name of the column and the second is the number of the row. |
| `getIndex`      | `() -> int`                         | Gets the index of the worksheet. |
| `getName`       | `() -> str`                         | Gets the name of the worksheet. |
| `getRow`        | `(int) -> array<mixed>`             | Gets all of the values for a specific row. Its only argument is the number of the row. |
| `hasCell`       | `(str, int) -> bool`                | Checks if a specific cell exists in the worksheet. The first argument is the name of the column and the second is the number of the row. |
| `hasColumn`     | `(str) -> bool`                     | Checks if a column exists in the worksheet. Its only argument is the name of the column. |
| `hasRow`        | `(int) -> bool`                     | Checks if a row exists in the worksheet. Its only argument is the number of the row. |
| `iterateColumn` | `(str) -> yield<int, mixed>`        | Yields each value in a specific column. Its only argument is the name of the column. The key is the number of the row and the value is the value of the cell. |
| `iterateRows`   | `() -> yield<int, map<str, mixed>>` | Yields each row in the worksheet. The key is the number of the row and the value is array of the value in each column. |

License
-------

This library is released under the MIT and Apache 2.0 licenses.
