<?php
/**
 * This file contains transient HttpConnection decorator
 *
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    LibfajrBundle
 * @subpackage Connection
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */

namespace SVT\LibfajrBundle\Connection;

use fajr\libfajr\pub\connection\HttpConnection;

/**
 * A http connection implementation that is not persistent.
 */
class TransientHttpConnection extends DecoratedHttpConnection
{

    /**
     * @var HttpConnection underlying connection used
     */
    private $realConnection;

    function __construct(HttpConnection $realConnection) {
        $this->realConnection = $realConnection;
    }

    /**
     * Return underlying connection.
     *
     * @param boolean $poll This argument has no effect
     * 
     * @return HttpConnection underlying connection
     */
    public function getRealConnection($poll = false) {
        return $this->realConnection;
    }

        public function __destruct() {
        $this->realConnection->clearCookies();
    }

}