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
use Yarhon\LinkGuardBundle\DependencyInjection\Compiler\RouteCollectionTransformerTaggedPass;
use Yarhon\LinkGuardBundle\Security\AccessMapBuilder;


/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteCollectionTransformerTaggedPassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var RouteCollectionTransformerTaggedPass
     */
    private $pass;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container->register(AccessMapBuilder::class);
        $this->pass = new RouteCollectionTransformerTaggedPass();
    }

    public function testProcess()
    {
        $tagName = 'link_guard.route_collection_transformer';

        $definition1 = new Definition();
        $definition1->addTag($tagName, ['priority' => 10]);

        $this->container->setDefinition('test1', $definition1);

        $definition2 = new Definition();
        $definition2->addTag($tagName, ['priority' => 20]);

        $this->container->setDefinition('test2', $definition2);

        $this->pass->process($this->container);

        $definition = $this->container->getDefinition(AccessMapBuilder::class);
        $methodCalls = $definition->getMethodCalls();

        $this->assertCount(1, $methodCalls);

        list($methodName, $arguments) = $methodCalls[0];

        $this->assertEquals('setRouteCollectionTransformers', $methodName);
        $this->assertCount(1, $arguments);

        $argument = $arguments[0];
        $this->assertInternalType('array', $argument);

        $this->assertEquals('test2', (string) $argument[0]);
        $this->assertEquals('test1', (string) $argument[1]);
    }
}
