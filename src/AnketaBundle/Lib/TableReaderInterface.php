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

/**
 * Interface for reading table structured data
 */
interface TableReaderInterface
{
    
    /**
     * Get contents of table header (column names).
     * @return array fields of the header
     * @throws Exception if an error has occured
     */
    public function getHeader();
    
    /**
     * Read a row of data from the data source
     * @return array fields of the row or false if no more are present
     * @throws Exception if an error has occured
     */
    public function readRow();
    
}