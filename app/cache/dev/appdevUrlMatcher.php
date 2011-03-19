<?php

/**
 * appdevUrlMatcher
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class appdevUrlMatcher extends Symfony\Component\Routing\Matcher\UrlMatcher
{
    /**
     * Constructor.
     */
    public function __construct(array $context = array(), array $defaults = array())
    {
        $this->context = $context;
        $this->defaults = $defaults;
    }

    public function match($url)
    {
        $url = $this->normalizeUrl($url);

        if (rtrim($url, '/') === '') {
            if (substr($url, -1) !== '/') {
                return array('_controller' => 'Symfony\Bundle\FrameworkBundle\Controller\RedirectController::urlRedirectAction', 'url' => $this->context['base_url'].$url.'/', 'permanent' => true, '_route' => 'homepage');
            }
            return array_merge($this->mergeDefaults(array(), array (  '_controller' => 'FMFI\\AnketaBundle\\Controller\\DefaultController::indexAction',)), array('_route' => 'homepage'));
        }

        return false;
    }
}
