<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NeonLight\SecureLinksBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use NeonLight\SecureLinksBundle\Twig\Node\RouteIfGrantedNode;
use NeonLight\SecureLinksBundle\Twig\TokenParser\RouteIfGrantedTokenParser;
use NeonLight\SecureLinksBundle\Twig\NodeVisitor\DiscoverRoutingFunctionNodeVisitor;
use NeonLight\SecureLinksBundle\Twig\RoutingRuntime;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RoutingExtension extends AbstractExtension
{
    public function __construct(array $options = [])
    {
        $defaults = [
            'referenceVarName' => 'route_reference',
        ];

        $options = array_merge($defaults, $options);

        RouteIfGrantedNode::setReferenceVarName($options['referenceVarName']);
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return [
            new RouteIfGrantedTokenParser(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getNodeVisitors()
    {
        return [
            new DiscoverRoutingFunctionNodeVisitor(),
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
