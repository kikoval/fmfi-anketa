<?php
/**
 * This file contains dependency injection extension for SVTLibfajrBundle.
 *
 * @copyright Copyright (c) 2011 The FMFI Anketa authors (see AUTHORS).
 * Use of this source code is governed by a license that can be
 * found in the LICENSE file in the project root directory.
 *
 * @package    LibfajrBundle
 * @subpackage DependencyInjection
 * @author     Martin Sucha <anty.sk+svt@gmail.com>
 */

namespace SVT\LibfajrBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use fajr\libfajr\pub\login\CosignServiceCookie;

/**
 * Dependency injection extension for SVTLibfajrBundle
 */
class SVTLibfajrExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        // load services
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('libfajr.xml');
        $loader->load('services.xml');

        $processor = new Processor();
        $configurationDefinition = new Configuration();
        $config = $processor->processConfiguration($configurationDefinition, $configs);

        if (empty($config['server'])) {
            throw new \LogicException("Server name (server) must be set");
        }
        $container->setParameter('libfajr.server.name', $config['server']);

        if (empty($config['cookie'])) {
            $config['cookie'] = 'cosign-filter-'.$config['server'];
        }
        $container->setParameter('libfajr.server.cookieName', $config['cookie']);

        if (empty($config['trace'])) {
            throw new \LogicException("Trace type (trace) must be set");
        }

        $container->setAlias('libfajr.trace', 'libfajr.trace.'.$config['trace']);
        
        $this->addLogin($container, $config);
        $this->addConnection($container, $config);
    }

    private function addLogin(ContainerBuilder $container, $config)
    {
        if (empty($config['login'])) {
            throw new \LogicException("login section must be configured");
        }

        $login = $config['login'];

        if (count($login) !== 1) {
            throw new \LogicException('Login definitions count must be exactly one.');
        }

        if (isset($login['cosign_proxy'])) {
            $mainId = 'libfajr.login.cosign_proxy';
            $container
                ->setDefinition($mainId, new DefinitionDecorator($mainId . '.template'))
                ->setArgument(0, $login['cosign_proxy']);
            $this->addAIS2CosignLogin($container, $mainId);
        }
        else if (isset($login['cosign_cookie'])) {
            $valueId = 'libfajr.login.cosign_cookie.value';
            $container
                ->setDefinition($valueId, new DefinitionDecorator($valueId . '.template'))
                ->setArgument(1, $login['cosign_cookie']);
            $mainId = 'libfajr.login.cosign_cookie';
            $container->setDefinition($mainId, new DefinitionDecorator($mainId . '.template'));
            $this->addAIS2CosignLogin($container, $mainId);
        }
        else {
            $keys = array_keys($login);
            throw new \LogicException(sprintf('Unknown login type: %s', $keys[0]));
        }
    }

    private function addAIS2CosignLogin(ContainerBuilder $container, $innerService)
    {
        $id = 'libfajr.login.cosign';
        $container
            ->setDefinition($id, new DefinitionDecorator($id . '.template'))
            ->setArgument(0, new Reference($innerService));
        $container->setAlias('libfajr.login', $id);
    }

    private function addConnection(ContainerBuilder $container, $config)
    {
        if (empty($config['connection'])) {
            throw new \LogicException("connection section must be configured");
        }

        $conn = $config['connection'];
        
        $container
            ->getDefinition('libfajr.http.connection.provider.curl')
            ->setArgument(1, $conn['curl']['directory'])
            ->setArgument(2, $conn['curl']['transient'])
            ->setArgument(3, $conn['curl']['user_agent']);
        
    }

}