<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Yarhon\LinkGuardBundle\Twig\Node\LinkNode;
use Yarhon\LinkGuardBundle\Twig\TokenParser\LinkTokenParser;
use Yarhon\LinkGuardBundle\Twig\NodeVisitor\DiscoverRoutingFunctionNodeVisitor;
use Yarhon\LinkGuardBundle\Twig\RoutingRuntime;

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
     * @var array
     */
    private $discoverFunctions;

    /**
     * RoutingExtension constructor.
     *
     * @param array $options
     * @param array $discoverFunctions
     */
    public function __construct(array $options = [], array $discoverFunctions = [])
    {
        $defaults = [
            'tag_name' => 'routeifgranted',
            'reference_var_name' => 'route_reference',
        ];

        $this->options = array_merge($defaults, $options);

        LinkNode::setReferenceVarName($this->options['reference_var_name']);

        $this->discoverFunctions = $discoverFunctions;
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return [
            new LinkTokenParser($this->options['tag_name']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeVisitors()
    {
        $nodeVisitors = [];

        if (0 !== count($this->discoverFunctions)) {
            $nodeVisitors[] = new DiscoverRoutingFunctionNodeVisitor($this->discoverFunctions, $this->options['reference_var_name'], $this->options['tag_name']);
        }

        return $nodeVisitors;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('route_guard_link', [RoutingRuntime::class, 'link']),
            new TwigFunction('route_guard_path', [RoutingRuntime::class, 'path']),
            new TwigFunction('route_guard_url', [RoutingRuntime::class, 'url']),
        ];
    }
}
