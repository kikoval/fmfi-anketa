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
 * Parses CSV lines.
 * @todo Make multibyte aware
 */
class CSVTableReader implements TableReaderInterface
{

    /** @var string the field delimiter character */
    private $delimiter;

    /** @var string string begin/end character */
    private $enclosure;

    /** @var string escape character */
    private $escape;

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
    public function __construct($fp, $delimiter=';',$enclosure='"',$escape='\\')
    {
        $this->fp = $fp;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
        $this->escape = $escape;
        $this->header = $this->readRow();
    }

    public function readRow()
    {
        $line = fgets($this->fp);
        if ($line === false) return false;
        $length = strlen($line);
        if ($length > 0 && $line[$length - 1] == "\n") {
            $line = substr($line, 0, $length - 1);
        }
        return $this->parseLine($line);
    }

    private function parseLine($line)
    {
        $pos = 0;
        $result = array();
        while ($pos < strlen($line)) {
            // parse fields
            $fieldValue = '';
            if ($line[$pos] == $this->enclosure) {
                // read until the end of field
                // skip the starting string mark
                $pos++;
                while ($line[$pos] != $this->enclosure) {
                    if ($line[$pos] == $this->escape) {
                        $pos++;
                    }
                    if ($pos >= strlen($line)) {
                        throw new Exception("Malformed CSV data, expecting field character.");
                    }
                    $fieldValue .= $line[$pos];
                    $pos++;
                    if ($pos >= strlen($line)) {
                        throw new Exception("Malformed CSV data, unclosed string.");
                    }
                }
                assert($line[$pos] == $this->enclosure);
                // skip the end of string mark
                $pos++;
            }
            else {
                // read until the end of field
                while ($pos < strlen($line) && $line[$pos] != $this->delimiter) {
                    $fieldValue .= $line[$pos];
                    $pos++;
                }
            }
            // Skip the field delimiter
            if ($pos < strlen($line)) {
                if ($line[$pos] != $this->delimiter) {
                    throw new Exception("Malformed CSV data, expecting field delimiter (position ".$pos.").");
                }
                $pos++;
            }
            // Append the field value
            $result[] = $fieldValue;
        }
        return $result;
    }

    public function getHeader() {
        return $this->header;
    }

}