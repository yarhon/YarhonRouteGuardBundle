<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Yarhon\LinkGuardBundle\DependencyInjection\Compiler\InjectTaggedServicesPass;
use Yarhon\LinkGuardBundle\Security\AccessMapBuilder;

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
        $this->container->register(AccessMapBuilder::class);
        $this->pass = new InjectTaggedServicesPass();
    }

    /**
     * @dataProvider processDataProvider
     */
    public function testProcess($destination, $tagName)
    {
        $definition1 = new Definition();
        $definition1->addTag($tagName, ['priority' => 10]);

        $this->container->setDefinition('test1', $definition1);

        $definition2 = new Definition();
        $definition2->addTag($tagName, ['priority' => 20]);

        $this->container->setDefinition('test2', $definition2);

        $definition = $this->container->getDefinition($destination[0]);
        $definition->addMethodCall($destination[1], [[]]);

        $this->pass->process($this->container);

        $methodCalls = $definition->getMethodCalls();

        //var_dump('m', $methodCalls);

        $this->assertCount(1, $methodCalls);

        list($methodName, $arguments) = $methodCalls[0];

        $this->assertEquals($destination[1], $methodName);
        $this->assertCount(1, $arguments);

        $argument = $arguments[0];
        $this->assertInternalType('array', $argument);

        $this->assertEquals('test2', (string) $argument[0]);
        $this->assertEquals('test1', (string) $argument[1]);
    }

    public function processDataProvider()
    {
        return [
            [
                [AccessMapBuilder::class, 'setRouteCollectionTransformers'],
                'yarhon_link_guard.route_collection_transformer',
            ],
            [
                [AccessMapBuilder::class, 'setAuthorizationProviders'],
                'yarhon_link_guard.authorization_provider',
            ],
        ];
    }
}
