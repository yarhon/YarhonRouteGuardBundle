<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Routing;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteMetadata
{
    /**
     * @var array
     */
    private $defaults;

    /**
     * @var array
     */
    private $variables;

    /**
     * @param array  $defaults
     * @param array  $variables
     */
    public function __construct(array $defaults, array $variables)
    {
        $this->defaults = $defaults;
        $this->variables = $variables;
    }

    /**
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }
}
