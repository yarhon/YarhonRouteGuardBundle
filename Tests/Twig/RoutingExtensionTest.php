<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;
use Yarhon\LinkGuardBundle\Twig\Node\LinkNode;
use Yarhon\LinkGuardBundle\Twig\TokenParser\LinkTokenParser;
use Yarhon\LinkGuardBundle\Twig\NodeVisitor\DiscoverRoutingFunctionNodeVisitor;
use Yarhon\LinkGuardBundle\Twig\RoutingRuntime;
use Yarhon\LinkGuardBundle\Twig\Extension\RoutingExtension;

class RoutingExtensionTest extends TestCase
{
    public function testGetFunctions()
    {
        $extension = new RoutingExtension();

        $functions = [
            new TwigFunction('url_if_granted', [RoutingRuntime::class, 'urlIfGranted']),
            new TwigFunction('path_if_granted', [RoutingRuntime::class, 'pathIfGranted']),
        ];

        $this->assertEquals($functions, $extension->getFunctions());
    }

    public function testGetTokenParsersDefaultOptions()
    {
        $defaults = [
            'tag_name' => 'routeifgranted',
            'reference_var_name' => 'route_reference',
        ];

        $extension = new RoutingExtension();

        $tokenParsers = [
            new LinkTokenParser($defaults['tag_name']),
        ];

        $this->assertEquals($tokenParsers, $extension->getTokenParsers());
        $this->assertAttributeEquals($defaults['reference_var_name'], 'referenceVarName', LinkNode::class);
    }

    public function testGetTokenParsersCustomOptions()
    {
        $options = [
            'tag_name' => 'foo',
            'reference_var_name' => 'bar',
        ];

        $extension = new RoutingExtension($options);

        $tokenParsers = [
            new LinkTokenParser($options['tag_name']),
        ];

        $this->assertEquals($tokenParsers, $extension->getTokenParsers());
        $this->assertAttributeEquals($options['reference_var_name'], 'referenceVarName', LinkNode::class);
    }

    public function testDiscoverFunctionsEmpty()
    {
        $extension = new RoutingExtension();
        $nodeVisitors = [];

        $this->assertEquals($nodeVisitors, $extension->getNodeVisitors());
    }

    public function testDiscoverFunctions()
    {
        $options = [
            'tag_name' => 'foo',
            'reference_var_name' => 'bar',
        ];

        $extension = new RoutingExtension($options, ['func']);
        $nodeVisitors = [
            new DiscoverRoutingFunctionNodeVisitor(['func'], $options['reference_var_name'], $options['tag_name']),
        ];

        $this->assertEquals($nodeVisitors, $extension->getNodeVisitors());
    }
}
