<?php

/**
 * @copyright Copyright (c) 2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */

namespace AnketaBundle\Lib;

use Exception;

/**
 * Resolve (and check presence of) columns in read tables
 */
class TableColumnResolver
{

    /** @var array */
    private $columnMapping;

    /** @var array */
    private $columnHeader;

    /** @var TableReaderInterface */
    private $reader;

    /**
     *
     * @param array $header table column headers
     */
    public function __construct(TableReaderInterface $reader)
    {
        $this->columnMapping = array();
        $this->columnHeader = $reader->getHeader();
        $this->reader = $reader;
    }

    public function mapColumnByTitle($title, $label)
    {
        $keys = array_keys($this->columnHeader, $title, true);
        if (count($keys) == 0) {
            throw new Exception('Column with such title does not exist: ' . $title);
        }
        if (count($keys) != 1) {
            throw new Exception('More than one column with such title exists: ' . $title);
        }
        $this->columnMapping[$label] = $keys[0];
    }

    public function mapColumnByIndex($index, $label)
    {
        if (count($this->columnHeader) <= $index) {
            throw new Exception('Column index too large: ' . $index);
        }
        $this->columnMapping[$label] = $index;
    }

    public function readRow() {
        $row = $this->reader->readRow();
        if ($row === false) return false;
        $ret = array();
        foreach ($this->columnMapping as $name => $index) {
            $ret[$name] = $row[$index];
        }
        return $ret;
    }



}