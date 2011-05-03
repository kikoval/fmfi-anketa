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

use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\connection\CurlConnection;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class CurlConnectionProvider implements HttpConnectionProviderInterface
{
    /** @var Session */
    private $session;

    /** @var string */
    private $directory;

    /** @var boolean */
    private $transient;

    /** @var string */
    private $userAgent;

    /** @var LoggerInterface */
    private $logger;

    function __construct(Session $session, $directory, $transient, $userAgent, LoggerInterface $logger = null) {
        $this->session = $session;
        $this->directory = $directory;
        $this->transient = $transient;
        $this->userAgent = $userAgent;
        $this->logger = $logger;
    }

    public function provideConnection()
    {
        // sanitize the id so it can easily be used as a file name
        $id = sha1((string) $this->session->getId());

        $cookieFile = $this->directory . DIRECTORY_SEPARATOR . 'cookies_'.$id;

        if ($this->logger !== null) {
            $this->logger->debug(sprintf('Providing curl connection using %s', $cookieFile));
        }

        $options = array(
            CURLOPT_FORBID_REUSE => false, // Keepalive konekcie
            CURLOPT_FOLLOWLOCATION => true, // Redirecty pri prihlasovani/odhlasovani
            CURLOPT_VERBOSE => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => true,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_ENCODING => 'gzip',
        );

        $connection = new CurlConnection($options, $cookieFile);

        if ($this->transient) {
            $connection = new TransientHttpConnection($connection);
        }

        return $connection;
    }
}