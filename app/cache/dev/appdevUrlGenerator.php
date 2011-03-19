<?php

/**
 * appdevUrlGenerator
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class appdevUrlGenerator extends Symfony\Component\Routing\Generator\UrlGenerator
{
    static protected $declaredRouteNames = array(
       'homepage' => true,
    );

    /**
     * Constructor.
     */
    public function __construct(array $context = array(), array $defaults = array())
    {
        $this->context = $context;
        $this->defaults = $defaults;
    }

    public function generate($name, array $parameters, $absolute = false)
    {
        if (!isset(self::$declaredRouteNames[$name])) {
            throw new \InvalidArgumentException(sprintf('Route "%s" does not exist.', $name));
        }

        $escapedName = str_replace('.', '__', $name);

        list($variables, $defaults, $requirements, $tokens) = $this->{'get'.$escapedName.'RouteInfo'}();

        return $this->doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $absolute);
    }

    protected function gethomepageRouteInfo()
    {
        return array(array (), array_merge($this->defaults, array (  '_controller' => 'FMFI\\AnketaBundle\\Controller\\DefaultController::indexAction',)), array (), array (  0 =>   array (    0 => 'text',    1 => '/',    2 => '',    3 => NULL,  ),));
    }
}
