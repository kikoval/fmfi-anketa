<?php
/**
 * This file contains trace implementation that forwards messages to logger
 *
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    LibfajrBundle
 * @subpackage Trace
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */

namespace SVT\LibfajrBundle\Trace;

use fajr\libfajr\pub\base\Trace;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class LogTrace implements Trace
{
    /** @var LoggerInterface */
    private $logger;

    function __construct(LoggerInterface $logger = null) {
        $this->logger = $logger;
    }


    public function addChild($header = "") {
        if ($this->logger !== null) {
            $this->logger->debug(sprintf('Trace - child: %s', $header));
        }
        return $this;
    }

    public function setHeader($header) {
        if ($this->logger !== null) {
            $this->logger->debug(sprintf('Trace - set header: %s', $header));
        }
    }

    public function tlog($text) {
        if ($this->logger !== null) {
            $this->logger->debug(sprintf('Trace: %s', $text));
        }
    }

    public function tlogData($string_data) {
        // Don't log data as it is high volume
    }

    public function tlogVariable($name, $variable) {
        if ($this->logger !== null) {
            $value = preg_replace("@\\\\'@", "'", var_export($variable, true));
            $this->logger->debug(sprintf('Trace: %s = %s', $name, $value));
        }
    }
}