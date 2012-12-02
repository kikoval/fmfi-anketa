<?php
/**
 * This file contains DataCollector for libfajr
 *
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    LibfajrBundle
 * @subpackage DataCollector
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */

namespace SVT\LibfajrBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use libfajr\trace\Trace;
use SVT\LibfajrBundle\Connection\DecoratedHttpConnection;
use libfajr\connection\HttpConnection;
use libfajr\connection\CurlConnection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class LibfajrDataCollector extends DataCollector
{
    /** @var Trace */
    private $trace;

    /** @var HttpConnection */
    private $httpConnection;

    function __construct(Trace $trace, HttpConnection $httpConnection)
    {
        $this->trace = $trace;
        $this->httpConnection = $httpConnection;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = array();

        $realConnection = $this->getRealConnection($this->httpConnection);
        if ($realConnection !== null && $realConnection instanceof CurlConnection) {
            $stats = $realConnection->getStats();
            $this->data['connection'] = array(
                'requestCount' => $stats->getRequestCount(),
                'errorCount' => $stats->getErrorCount(),
                'downloadedBytes' => $stats->getDownloadedBytes(),
                'totalTime' => $stats->getTotalTime(),
            );
        }
    }

    private function getRealConnection(HttpConnection $connection)
    {
        while ($connection !== null &&
                $connection instanceof DecoratedHttpConnection) {
            $connection = $connection->getRealConnection(true);
        }

        return $connection;
    }

    public function getConnection()
    {
        if (!isset($this->data['connection'])) {
            return null;
        }
        return $this->data['connection'];
    }

    public function getName()
    {
        return 'libfajr';
    }

}