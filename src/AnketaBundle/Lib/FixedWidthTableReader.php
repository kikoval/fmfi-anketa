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
 * Parses fixed width tables.
 * @todo Make multibyte aware 
 */
class FixedWidthTableReader implements TableReaderInterface
{
    
    /** @var resource file handle */
    private $fp;
    
    /** @var array */
    private $columns;
    
    /** @var array */
    private $header;
    
    /**
     *
     * @param type $fp file handle to read from
     * @param type $delimiter
     * @param type $enclosure
     * @param type $escape 
     */
    public function __construct($fp)
    {
        $this->fp = $fp;
        $this->columns = array();
        $this->header = array();
        $this->parseHeader();
    }
    
    private function parseHeader()
    {
        $firstLine = $this->readLine();
        $secondLine = $this->readLine();
        if ($firstLine === false || $secondLine === false) {
            throw new Exception('Bad header');
        }
        $columnBounds = preg_split("/[\s]+/", $secondLine, null, PREG_SPLIT_OFFSET_CAPTURE);
        foreach ($columnBounds as $columnBound) {
            if (strlen($columnBound[0]) == 0) {
                continue;
            }
            $this->columns[] = array($columnBound[1], strlen($columnBound[0]));
        }
        $this->header = $this->parseLine($firstLine);
    }
    
    private function readLine()
    {
        $line = fgets($this->fp);
        if ($line === false) return false;
        $length = strlen($line);
        if ($length > 0 && $line[$length - 1] == "\n") {
            $line = substr($line, 0, $length - 1);
        }
        return $line;
    }
    
    public function readRow()
    {
       $line = $this->readLine();
       if ($line === false) return false;
       return $this->parseLine($line);
    }
    
    private function parseLine($line)
    {
        $vals = array();
        foreach ($this->columns as $column) {
            if (strlen($line) < $column[1]) {
                return false;
            }
            $vals[] = trim(substr($line, $column[0], $column[1]));
        }
        return $vals;
    }

    public function getHeader() {
        return $this->header;
    }

    
}