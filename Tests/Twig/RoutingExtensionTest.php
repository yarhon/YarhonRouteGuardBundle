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
            'reference_var_name' => 'ref',
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
            'reference_var_name' => 'bar',
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
            'reference_var_name' => 'bar',
            'discover_routing_functions' => true,
        ];

        $extension = new RoutingExtension($options);
        $nodeVisitors = [
            new DiscoverRoutingFunctionNodeVisitor($options['reference_var_name'], $options['tag_name']),
        ];

        $this->assertEquals($nodeVisitors, $extension->getNodeVisitors());
    }

    public function testRouteNode()
    {
        $defaults = [
            'reference_var_name' => 'ref',
        ];

        $extension = new RoutingExtension();

        $this->assertAttributeEquals($defaults['reference_var_name'], 'referenceVarName', RouteNode::class);

        $options = [
            'reference_var_name' => 'bar',
        ];

        $extension = new RoutingExtension($options);

        $this->assertAttributeEquals($options['reference_var_name'], 'referenceVarName', RouteNode::class);
    }
}
