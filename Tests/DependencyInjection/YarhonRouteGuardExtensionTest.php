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
use Yarhon\RouteGuardBundle\Cache\DataCollector\RouteCollectionDataCollector;
use Yarhon\RouteGuardBundle\Twig\Extension\RoutingExtension;
use Yarhon\RouteGuardBundle\Security\TestProvider\ProviderInterface;
use Yarhon\RouteGuardBundle\Security\TestResolver\SymfonySecurityResolverInterface;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver\ArgumentValueResolverInterface;
use Yarhon\RouteGuardBundle\Security\AuthorizationChecker\AuthorizationCheckerInterface;
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
            'data_collector' => ['ignore_controllers' => ['class']],
            'twig' => ['tag_name' => 'test'],
        ];

        $extension = new YarhonRouteGuardExtension();
        $this->container->loadFromExtension($extension->getAlias(), $config);

        $this->container->getCompilerPassConfig()->setOptimizationPasses([]);
        $this->container->getCompilerPassConfig()->setRemovingPasses([]);
        $this->container->compile();

        $argument = $this->container->getDefinition(RouteCollectionDataCollector::class)->getArgument(2);
        $this->assertInternalType('array', $argument);
        $this->assertArrayHasKey('ignore_controllers', $argument);
        $this->assertArraySubset(['class'], $argument['ignore_controllers']);

        $argument = $this->container->getDefinition(RoutingExtension::class)->getArgument(0);
        $this->assertInternalType('array', $argument);
        $this->assertArraySubset(['tag_name' => 'test'], $argument);
    }

    /**
     * @dataProvider registerAutoConfigurationDataProvider
     */
    public function testRegisterAutoConfiguration($interface, $tag)
    {
        $extension = new YarhonRouteGuardExtension();
        $this->container->loadFromExtension($extension->getAlias(), []);

        $this->container->getCompilerPassConfig()->setOptimizationPasses([]);
        $this->container->getCompilerPassConfig()->setRemovingPasses([]);
        $this->container->compile();

        $autoconfigured = $this->container->getAutoconfiguredInstanceof();

        $this->assertArrayHasKey($interface, $autoconfigured);
        $tags = array_keys($autoconfigured[$interface]->getTags());
        $this->assertEquals([$tag], $tags);
    }

    public function registerAutoConfigurationDataProvider()
    {
        return [
            [ProviderInterface::class, 'yarhon_route_guard.test_provider'],
            [SymfonySecurityResolverInterface::class, 'yarhon_route_guard.test_resolver.symfony_security'],
            [ArgumentValueResolverInterface::class, 'yarhon_route_guard.argument_value_resolver'],
            [AuthorizationCheckerInterface::class, 'yarhon_route_guard.authorization_checker'],
        ];
    }

    /**
     * @dataProvider publicServicesDataProvider
     */
    public function testPublicServices($serviceId)
    {
        $this->container->setParameter('kernel.cache_dir', 'test_cache_dir');

        $this->container->register('request_stack')->setSynthetic(true);
        $this->container->register('router.default')->setSynthetic(true);
        $this->container->register('security.authorization_checker')->setSynthetic(true);

        $this->container->compile();

        $this->assertTrue($this->container->has($serviceId));
    }

    public function publicServicesDataProvider()
    {
        return [
            ['yarhon_route_guard.authorized_url_generator'],
            ['yarhon_route_guard.route_authorization_checker'],
            ['yarhon_route_guard.test_loader'],
        ];
    }
}
