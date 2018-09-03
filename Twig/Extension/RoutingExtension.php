<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Yarhon\RouteGuardBundle\Twig\Node\RouteNode;
use Yarhon\RouteGuardBundle\Twig\TokenParser\RouteTokenParser;
use Yarhon\RouteGuardBundle\Twig\NodeVisitor\DiscoverRoutingFunctionNodeVisitor;
use Yarhon\RouteGuardBundle\Twig\RoutingRuntime;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RoutingExtension extends AbstractExtension
{
    /**
     * @var array
     */
    private $options;

    /**
     * RoutingExtension constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $defaults = [
            'tag_name' => 'route',
            'reference_var_name' => 'ref',
            'discover_routing_functions' => false,
        ];

        $this->options = array_merge($defaults, $options);

        RouteNode::setReferenceVarName($this->options['reference_var_name']);
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return [
            new RouteTokenParser($this->options['tag_name'], $this->options['discover_routing_functions']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeVisitors()
    {
        $nodeVisitors = [];

        if ($this->options['discover_routing_functions']) {
            $nodeVisitors[] = new DiscoverRoutingFunctionNodeVisitor($this->options['reference_var_name'], $this->options['tag_name']);
        }

        return $nodeVisitors;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('route_guard_route', [RoutingRuntime::class, 'route']),
            new TwigFunction('route_guard_path', [RoutingRuntime::class, 'path']),
            new TwigFunction('route_guard_url', [RoutingRuntime::class, 'url']),
        ];
    }
}
