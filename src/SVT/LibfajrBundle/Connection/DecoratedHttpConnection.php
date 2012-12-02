<?php
/**
 * This file contains lazy HttpConnection implementation
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

use libfajr\connection\HttpConnection;
use libfajr\trace\Trace;

abstract class DecoratedHttpConnection implements HttpConnection
{

    /**
     * Get real connection used to connect to server.
     *
     *
     * @param boolean $poll If true, do not execute any expensive operation,
     *                      and return null if such operation would be required.
     *                      Default is false.
     *
     * @return HttpConnection|null real http connection or null if one is not
     *                             available
     */
    public abstract function getRealConnection($poll = false);

    public function addCookie($name, $value, $expire, $path, $domain, $secure = true, $tailmatch = false)
    {
        return $this->getRealConnection()
                ->addCookie($name, $value, $expire, $path, $domain, $secure, $tailmatch);
    }

    public function clearCookies()
    {
        return $this->getRealConnection()
                ->clearCookies();
    }

    public function get(Trace $trace, $url)
    {
        return $this->getRealConnection()
                ->get($trace, $url);
    }

    public function post(Trace $trace, $url, $data)
    {
        return $this->getRealConnection()
                ->post($trace, $url, $data);
    }

    public function close()
    {
        $conn = $this->getRealConnection(true);
        if ($conn === null) return;
        $conn->close();
    }

}