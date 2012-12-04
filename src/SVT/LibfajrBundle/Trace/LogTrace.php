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

use libfajr\trace\Trace;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class LogTrace implements Trace
{
    /** @var LoggerInterface */
    private $logger;

    function __construct(LoggerInterface $logger = null) {
        $this->logger = $logger;
    }


    public function addChild($message, array $tags = null) {
        if ($this->logger !== null) {
            $this->logger->debug(sprintf('Trace - child: %s', $message));
        }
        return $this;
    }

    public function tlog($text, array $tags = null) {
        if ($this->logger !== null) {
            $this->logger->debug(sprintf('Trace: %s', $text));
        }
    }

    public function tlogVariable($name, $variable, array $tags = null) {
        if ($this->logger !== null) {
            $value = preg_replace("@\\\\'@", "'", var_export($variable, true));
            // premenne tiez urveme, kedze niektore mozu obsahovat pomerne velke data
            // to je bug v libfajr, na data by sa malo pouzivat tlogData
            if (strlen($value) >= 100) {
                $value = substr($value, 0, 97).'...';
            }
            // escapneme nove riadky atd. nech sa da dobre grepovat
            $value = addcslashes($value, "\r\n\t[]");
            $this->logger->debug(sprintf('Trace: %s = %s', $name, $value));
        }
    }
}