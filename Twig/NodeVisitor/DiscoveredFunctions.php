<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Twig\NodeVisitor;

use Twig\Node\Node;

class DiscoveredFunctions
{
    /**
     * @var array
     */
    private $functions = [
        'url',
        'path',
    ];

    /**
     * @param string $functionName
     *
     * @return bool
     */
    public function has($functionName)
    {
        return in_array($functionName, $this->functions);
    }

    /**
     * @param string $functionName
     * @param Node[] $functionArguments
     *
     * @return array array of 2 elements: RouteExpression arguments and generateAs parameters
     */
    public function resolveArguments($functionName, $functionArguments)
    {
        $arguments = [$functionArguments[0]];
        $generateAs = [$functionName];

        if (isset($functionArguments[1])) {
            $arguments[1] = $functionArguments[1];
        }

        if (isset($functionArguments[2])) {
            $generateAs[1] = $functionArguments[2];
        }

        return [$arguments, $generateAs];
    }

    public function getFunctions()
    {
        return $this->functions;
    }
}
