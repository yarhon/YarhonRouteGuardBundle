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
use NeonLight\SecureLinksBundle\Twig\TokenParser\IfRouteGrantedTokenParser;
use NeonLight\SecureLinksBundle\Twig\NodeVisitor\RoutingFunctionNodeVisitor;


/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SecureLinksExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return [
            new IfRouteGrantedTokenParser(),
        ];
    }

    public function getOperators()
    {
        /**
         * Returns a list of operators to add to the existing list.
         *
         * @return array<array> First array of unary operators, second array of binary operators
         */

        return array();
    }

    public function getNodeVisitors()
    {
        return [
            new RoutingFunctionNodeVisitor(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('is_route_granted', [$this, 'isRouteGranted']),
        ];
    }

    public function isRouteGranted()
    {
        return true;
    }
}
