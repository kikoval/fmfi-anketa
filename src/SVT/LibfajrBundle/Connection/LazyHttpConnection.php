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

class LazyHttpConnection extends DecoratedHttpConnection
{
    /** @var HttpConnectionProviderInterface to use to provide http connection */
    private $provider;

    /** @var HttpConnection actually used connection */
    protected $realConnection;

    public function __construct(HttpConnectionProviderInterface $provider)
    {
        $this->provider = $provider;
        $this->realConnection = null;
    }

    /**
     * Get real connection used to connect to server.
     *
     * Calls the provider if no connection has been provided yet,
     * so this function always returns an instance of HttpConnection
     * or throws exception
     *
     * @param boolean $poll if false, do not create the connection, if it is
     *                      not already created
     *
     * @return HttpConnection|null real http connection or null if the
     *                             connection is not ready and $poll is true
     */
    public function getRealConnection($poll = false)
    {
        if ($this->realConnection !== null || $poll) {
            return $this->realConnection;
        }

        $rc = $this->provider->provideConnection();

        if (!($rc instanceof HttpConnection)) {
            throw new \Exception("Provider did not return an instance of HttpConnection");
        }

        return $this->realConnection = $rc;
    }

}