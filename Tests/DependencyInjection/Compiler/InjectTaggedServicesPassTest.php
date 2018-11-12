<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yarhon\RouteGuardBundle\DependencyInjection\Compiler\InjectTaggedServicesPass;
use Yarhon\RouteGuardBundle\Security\TestProvider\ProviderAggregate;
use Yarhon\RouteGuardBundle\Security\TestResolver\SymfonySecurityResolver;
use Yarhon\RouteGuardBundle\Controller\ArgumentResolver;
use Yarhon\RouteGuardBundle\Security\AuthorizationChecker\DelegatingAuthorizationChecker;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class InjectTaggedServicesPassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var InjectTaggedServicesPass
     */
    private $pass;

    public function setUp()
    {
        $this->container = new ContainerBuilder();

        $this->container->register(ProviderAggregate::class)->addArgument([]);

        $this->container->register(SymfonySecurityResolver::class)->addArgument([]);

        $this->container->register(ArgumentResolver::class)->setArguments([null, null, null, []]);

        $this->container->register(DelegatingAuthorizationChecker::class)->addArgument([]);

        $this->pass = new InjectTaggedServicesPass();
    }

    /**
     * @dataProvider injectArgumentsDataProvider
     */
    public function testInjectArguments($destination, $tagName)
    {
        $this->container->register('test1')->addTag($tagName, ['priority' => 10]);
        $this->container->register('test2')->addTag($tagName, ['priority' => 20]);

        $this->pass->process($this->container);

        $definition = $this->container->getDefinition($destination[0]);

        $argument = $definition->getArgument($destination[1]);

        $this->assertInternalType('array', $argument);

        $this->assertEquals('test2', (string) $argument[0]);
        $this->assertEquals('test1', (string) $argument[1]);
    }

    public function injectArgumentsDataProvider()
    {
        return [
            [
                [ProviderAggregate::class, 0],
                'yarhon_route_guard.test_provider',
            ],
            [
                [ArgumentResolver::class, 3],
                'yarhon_route_guard.argument_value_resolver',
            ],
            [
                [SymfonySecurityResolver::class, 0],
                'yarhon_route_guard.test_resolver.symfony_security',
            ],
            [
                [DelegatingAuthorizationChecker::class, 0],
                'yarhon_route_guard.authorization_checker',
            ],
        ];
    }
}
