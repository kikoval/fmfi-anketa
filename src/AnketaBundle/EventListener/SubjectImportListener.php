<?php
/**
 * This file contains subject import listener
 *
 * @copyright Copyright (c) 2011-2013 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    Anketa
 * @subpackage EventListener
 */

namespace AnketaBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;

use AnketaBundle\Controller\SubjectImportController;
use AnketaBundle\Entity\User;
use AnketaBundle\Entity\UserSeason;


/**
 * Imports users' subjects from AIS when a controller implementing
 * SubjectImportController interface is called
 */
class SubjectImportListener
{
    /** @var ContainerInterface */
    private $container;

    /** @var EntityManager */
    private $em;

    /** @var array */
    private $userSources;

    public function __construct(ContainerInterface $container, array $userSources)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine.orm.entity_manager');
        $this->userSources = $userSources;
    }

    /**
     *
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();

        /*
         * $controller passed can be either a class or a Closure. This is not
         * usual in Symfony2 but it may happen.
         * If it is a class, it comes in array format
         */
        if (!is_array($controller)) {
            return;
        }

        if ($controller[0] instanceof SubjectImportController) {
            $user = $this->container->get('security.context')->getToken()
                    ->getUser();
            $this->loadUserInfo($user);
        }
    }

    /**
     * Load user info and user's subjects in necessary.
     *
     * @param User $user
     */
    private function loadUserInfo(User $user)
    {
        $activeSeason = $this->em->getRepository('AnketaBundle:Season')
                ->getActiveSeason();
        $userSeason = $this->em->getRepository('AnketaBundle:UserSeason')
                ->findOneBy(array('user' => $user,
                                  'season' => $activeSeason));

        // "$load[X][Y]" == "service X should load user attribute Y"
        $load = array();

        if (!$userSeason->getLoadedFromAis()) {
            $load[$this->userSources['isStudent']]['isStudent'] = TRUE;
            $load[$this->userSources['subjects']]['subjects'] = TRUE;
            $userSeason->setLoadedFromAis(TRUE);
        }

        foreach ($load as $service => $attributes) {
            $this->container->get($service)->load($userSeason, $attributes);
        }

        $this->em->flush();
    }
}
