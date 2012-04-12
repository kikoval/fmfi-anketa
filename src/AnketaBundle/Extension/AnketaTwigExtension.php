<?php

namespace AnketaBundle\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use AnketaBundle\Entity\User;

class AnketaTwigExtension extends \Twig_Extension {
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getGlobals() {
        return array(
            'menu' => array(
                'hlasovanie' => $this->container->get('anketa.menu.hlasovanie'),
                'statistics' => $this->container->get('anketa.menu.statistics'),
            ),
            'access' => array(
                'hlasovanie' => $this->container->get('anketa.access.hlasovanie'),
                'statistics' => $this->container->get('anketa.access.statistics'),
            ),
        );
    }

    public function getFunctions()
    {
        return array(
            'analytics' => new \Twig_Function_Method($this, 'getAnalytics', array('is_safe' => array('html'))),
            'user_bar' => new \Twig_Function_Method($this, 'getUserBar', array('is_safe' => array('html'))),
        );
    }

    public function getAnalytics() {
        $parameter = 'google_analytics_tracking_code';
        if ($this->container->hasParameter($parameter)) {
            $ga = $this->container->getParameter($parameter);
        }
        else {
            $ga = null;
        }
        return $this->container->get('templating')->render('AnketaBundle::analytics.html.twig',
                        array('ga_tracking_code' => $ga));
    }

    public function getUserBar() {
        $user = null;
        $token = $this->container->get('security.context')->getToken();
        if ($token !== null) {
            $user = $token->getUser();
        }
        return $this->container->get('templating')->render('AnketaBundle::user_bar.html.twig',
                             array('user' => ($user instanceof User ? $user : null)));
    }

    public function getName()
    {
        return 'anketa';
    }

}
