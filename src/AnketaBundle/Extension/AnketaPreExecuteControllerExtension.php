<?php

namespace AnketaBundle\Extension;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class AnketaPreExecuteControllerExtension {

    public function onKernelController(FilterControllerEvent $event) {
        if (HttpKernelInterface::MASTER_REQUEST === $event->getRequestType()) {
            $controllers = $event->getController();
            if (is_array($controllers)) {
                $controller = $controllers[0];

                if (is_object($controller) && method_exists($controller, 'preExecute')) {
                    // I hate Symfony...
                    $result = $controller->preExecute();
                    if ($result instanceof Response) {
                        $event->setController(function () use ($result) {
                            return $result;
                        });
                    }
                }
            }
        }
    }

}
