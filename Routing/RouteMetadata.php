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
     * @var string
     */
    private $controllerName;

    /**
     * @var array
     */
    private $defaults;

    /**
     * @var array
     */
    private $variables;

    /**
     * RouteMetadata constructor.
     *
     * @param array  $defaults
     * @param array  $variables
     * @param string $controllerName
     */
    public function __construct(array $defaults, array $variables, $controllerName = null)
    {
        $this->defaults = $defaults;
        $this->variables = $variables;
        $this->controllerName = $controllerName;
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return $this->controllerName;
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
