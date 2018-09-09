<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yarhon\RouteGuardBundle\DependencyInjection\YarhonRouteGuardExtension;
use Yarhon\RouteGuardBundle\Routing\RouteCollection\RemoveIgnoredTransformer;
use Yarhon\RouteGuardBundle\Twig\Extension\RoutingExtension;
use Yarhon\RouteGuardBundle\YarhonRouteGuardBundle;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class YarhonRouteGuardExtensionTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    public function setUp()
    {
        $extension = new YarhonRouteGuardExtension();
        $this->container = new ContainerBuilder();
        $this->container->registerExtension($extension);

        $this->container->loadFromExtension($extension->getAlias());

        // $bundle = new YarhonRouteGuardBundle();
        // $bundle->build($this->container);
    }

    public function testSetConfigParameters()
    {
        $config = [
            'ignore_controllers' => ['test'],
            'twig' => ['tag_name' => 'test'],
        ];

        $extension = new YarhonRouteGuardExtension();
        $this->container->loadFromExtension($extension->getAlias(), $config);

        $this->container->getCompilerPassConfig()->setOptimizationPasses([]);
        $this->container->getCompilerPassConfig()->setRemovingPasses([]);
        $this->container->compile();

        $argument = $this->container->getDefinition(RemoveIgnoredTransformer::class)->getArgument(0);
        $this->assertArraySubset([0 => 'test'], $argument);

        $argument = $this->container->getDefinition(RoutingExtension::class)->getArgument(0);
        $this->assertArraySubset(['tag_name' => 'test'], $argument);

        $this->markTestIncomplete('Watch for config changes.');
    }

    public function testPrivateServices()
    {
        $services = [
            'Yarhon\RouteGuardBundle\Security\AccessMapBuilder',
            'Yarhon\RouteGuardBundle\Security\RouteAuthorizationChecker',
        ];

        $aliases = [
            'yarhon_route_guard.route_authorization_checker',
            'Yarhon\RouteGuardBundle\Security\RouteAuthorizationCheckerInterface',
        ];

        $this->container->getCompilerPassConfig()->setOptimizationPasses([]);
        $this->container->getCompilerPassConfig()->setRemovingPasses([]);
        $this->container->compile();

        foreach ($services as $id) {
            $this->assertTrue($this->container->hasDefinition($id), $id);
        }

        foreach ($aliases as $id) {
            $this->assertTrue($this->container->hasAlias($id), $id);
        }

        $this->markTestIncomplete('Watch for service changes.');
    }

    public function testPublicServices()
    {
        $this->container->setParameter('kernel.cache_dir', 'test_cache_dir');

        $this->container->register('router.default')->setSynthetic(true);
        $this->container->register('security.authorization_checker')->setSynthetic(true);

        $services = [
            'yarhon_route_guard.route_authorization_checker',
            'yarhon_route_guard.route_test_resolver',
            'yarhon_route_guard.authorized_url_generator',
        ];

        //$this->container->getCompilerPassConfig()->setOptimizationPasses([]);
        //$this->container->getCompilerPassConfig()->setRemovingPasses([]);
        $this->container->compile();

        foreach ($services as $id) {
            $this->assertTrue($this->container->has($id), $id);
        }

        $this->markTestIncomplete('Watch for service changes.');
    }

    private function getDefinitions()
    {
        $defined = array_keys($this->container->getDefinitions());
        $defined = array_diff($defined, ['service_container', 'kernel', 'security.authorization_checker']);
        sort($defined);

        return $defined;
    }

    private function getAliases()
    {
        $defined = array_keys($this->container->getAliases());
        $defined = array_diff($defined, ['Psr\Container\ContainerInterface', 'Symfony\Component\DependencyInjection\ContainerInterface']);
        sort($defined);

        return $defined;
    }

    private function getParameters()
    {
        $defined = $this->container->getParameterBag()->all();
        ksort($defined);

        return $defined;
    }
}
