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
     * RoutingExtension constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $defaults = [
             // 'tagName' => 'routeifgranted',
            'referenceVarName' => 'route_reference',
        ];

        $this->options = array_merge($defaults, $options);

        LinkNode::setReferenceVarName($this->options['referenceVarName']);
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return [
            new LinkTokenParser(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeVisitors()
    {
        return [
            new DiscoverRoutingFunctionNodeVisitor($this->options['referenceVarName']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('url_if_granted', [RoutingRuntime::class, 'urlIfGranted']),
            new TwigFunction('path_if_granted', [RoutingRuntime::class, 'pathIfGranted']),
        ];
    }
}
