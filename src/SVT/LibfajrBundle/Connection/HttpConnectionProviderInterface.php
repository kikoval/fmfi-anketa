<?php
/**
 * This file contains interface of http connection providers
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

interface HttpConnectionProviderInterface
{
    /**
     * Provide a connection to be used to connect to a server
     *
     * @return HttpConnection
     */
    function provideConnection();
}