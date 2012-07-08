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
 * Appends multiple tables one after another
 */
class ConcatTableReader implements TableReaderInterface
{
    
    /** @var array */
    private $readers;
    
    /** @var int */
    private $currentReader;
    
    
    public function __construct(array $readers)
    {
        $this->readers = $readers;
        $this->currentReader = 0;
    }
    
    public function readRow()
    {
        if ($this->currentReader >= count($this->readers)) return false;
        $ret = $this->readers[$this->currentReader]->readRow();
        if ($ret === false) {
            $this->currentReader++;
            return $this->readRow();
        }
        return $ret;
    }

    public function getHeader() {
        return $this->readers[0]->getHeader();
    }
    
}