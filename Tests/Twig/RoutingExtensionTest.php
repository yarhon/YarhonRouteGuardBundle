<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;
use Yarhon\RouteGuardBundle\Twig\Node\RouteNode;
use Yarhon\RouteGuardBundle\Twig\TokenParser\RouteTokenParser;
use Yarhon\RouteGuardBundle\Twig\NodeVisitor\DiscoverRoutingFunctionNodeVisitor;
use Yarhon\RouteGuardBundle\Twig\RoutingRuntime;
use Yarhon\RouteGuardBundle\Twig\Extension\RoutingExtension;

class RoutingExtensionTest extends TestCase
{
    public function testGetFunctions()
    {
        $extension = new RoutingExtension();

        $functions = [
            new TwigFunction('route_guard_route', [RoutingRuntime::class, 'route']),
            new TwigFunction('route_guard_path', [RoutingRuntime::class, 'path']),
            new TwigFunction('route_guard_url', [RoutingRuntime::class, 'url']),
        ];

        $this->assertEquals($functions, $extension->getFunctions());
    }

    public function testGetTokenParsersDefaultOptions()
    {
        $defaults = [
            'tag_name' => 'route',
            'tag_variable_name' => '_route',
            'discover_routing_functions' => false,
        ];

        $extension = new RoutingExtension();

        $tokenParsers = [
            new RouteTokenParser($defaults['tag_name'], $defaults['discover_routing_functions']),
        ];

        $this->assertEquals($tokenParsers, $extension->getTokenParsers());
    }

    public function testGetTokenParsersCustomOptions()
    {
        $options = [
            'tag_name' => 'foo',
            'tag_variable_name' => 'bar',
            'discover_routing_functions' => true,
        ];

        $extension = new RoutingExtension($options);

        $tokenParsers = [
            new RouteTokenParser($options['tag_name'], $options['discover_routing_functions']),
        ];

        $this->assertEquals($tokenParsers, $extension->getTokenParsers());
    }

    public function testDiscoverNodeVisitorEmpty()
    {
        $extension = new RoutingExtension();
        $nodeVisitors = [];

        $this->assertEquals($nodeVisitors, $extension->getNodeVisitors());
    }

    public function testDiscoverNodeVisitor()
    {
        $options = [
            'tag_name' => 'foo',
            'tag_variable_name' => 'bar',
            'discover_routing_functions' => true,
        ];

        $extension = new RoutingExtension($options);
        $nodeVisitors = [
            new DiscoverRoutingFunctionNodeVisitor($options['tag_name'], $options['tag_variable_name']),
        ];

        $this->assertEquals($nodeVisitors, $extension->getNodeVisitors());
    }

    public function testRouteNode()
    {
        $defaults = [
            'tag_variable_name' => '_route',
        ];

        $extension = new RoutingExtension();

        $this->assertAttributeEquals($defaults['tag_variable_name'], 'variableName', RouteNode::class);

        $options = [
            'tag_variable_name' => 'bar',
        ];

        $extension = new RoutingExtension($options);

        $this->assertAttributeEquals($options['tag_variable_name'], 'variableName', RouteNode::class);
    }
}
