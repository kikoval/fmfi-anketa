<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * ----
 * Modified for Anketa project
 *
 * Copyright (c) 2012 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 */

namespace AnketaBundle\EventListener;

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Exception;

/**
 * ExceptionListener.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ExceptionListener implements EventSubscriberInterface
{
    private $controller;
    private $logger;

    public function __construct($controller, LoggerInterface $logger = null)
    {
        $this->controller = $controller;
        $this->logger = $logger;
    }

    protected function logException(Exception $exception, $prefix)
    {
        $flat = FlattenException::create($exception);
        $message = sprintf("%s%s: %s", $prefix, get_class($exception), $flat->getMessage());
        foreach ($flat->getTrace() as $index => $trace) {
            $message .= "\n";
            if ($index > 0) {
                $message .= ' called';
            }
            if (!empty($trace['class']) || !empty($trace['function'])) {
                $message .= ' at ';
            }
            if (!empty($trace['class'])) {
                $message .= sprintf('%s -> ', $trace['class']);
            }
            if (!empty($trace['function'])) {
                $message .= $trace['function'];
            }
            if (!empty($trace['file'])) {
                $message .= sprintf(' in %s', $trace['file']);
            }
            if (!empty($trace['line'])) {
                $message .= sprintf(' on line %s', $trace['line']);
            }
        }

        if ($this->logger !== null) {
            if (!$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500) {
                $this->logger->crit($message);
            } else {
                $this->logger->err($message);
            }
        }
        else {
            error_log($message);
        }
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        static $handling;

        if (true === $handling) {
            return false;
        }

        $handling = true;

        $exception = $event->getException();
        $request = $event->getRequest();

        $this->logException($exception, 'Uncaught exception ');

        $logger = $this->logger instanceof DebugLoggerInterface ? $this->logger : null;

        $flattenException = FlattenException::create($exception);
        if ($exception instanceof HttpExceptionInterface) {
            $flattenException->setStatusCode($exception->getStatusCode());
            $flattenException->setHeaders($exception->getHeaders());
        }

        $attributes = array(
            '_controller' => $this->controller,
            'exception'   => $flattenException,
            'logger'      => $logger,
            'format'      => $request->getRequestFormat(),
        );

        $request = $request->duplicate(null, null, $attributes);

        try {
            $response = $event->getKernel()->handle($request, HttpKernelInterface::SUB_REQUEST, true);
        } catch (\Exception $e) {
            $this->logException($e, 'While handling exception we got another exception ');

            // set handling to false otherwise it wont be able to handle further more
            $handling = false;

            // re-throw the exception as this is a catch-all
            throw $exception;
        }

        $event->setResponse($response);

        $handling = false;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onKernelException', -128),
        );
    }
}
