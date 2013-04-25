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
 * Parses AIS .tbl files.
 * @todo Make multibyte aware
 */
class AISDelimitedTableReader implements TableReaderInterface
{

    /** @var resource file handle */
    private $fp;

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
        $this->parseHeader();
    }

    private function parseHeader()
    {
        $this->header = array();
        while (($line = $this->readLine()) !== false) {
            if ($line == '.') break;
            $parsed = $this->parseLine($line);
            $this->header[] = $parsed[0];
        }
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
        return preg_split('/\|/', $line);
    }

    public function getHeader() {
        return $this->header;
    }

}