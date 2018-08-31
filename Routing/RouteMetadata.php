<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Routing;

use Symfony\Component\Routing\Route;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteMetadata implements RouteMetadataInterface
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
     * @var bool
     */
    private $hasHost;

    /**
     * @var bool
     */
    private $hasStaticHost;

    /**
     * @var string|null
     */
    private $staticHost;

    /**
     * RouteMetadata constructor.
     *
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        $this->controllerName = $route->getDefault('_controller');
        $this->defaults = $route->getDefaults();

        $compiledRoute = $route->compile();
        $this->variables = $compiledRoute->getVariables();

        $this->hasHost = (bool) $route->getHost();
        $this->hasStaticHost = $this->hasHost && !$compiledRoute->getHostVariables();
        $this->staticHost = $this->hasStaticHost ? $route->getHost() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHost()
    {
        return $this->hasHost;
    }

    /**
     * {@inheritdoc}
     */
    public function hasStaticHost()
    {
        return $this->hasStaticHost;
    }

    /**
     * {@inheritdoc}
     */
    public function getStaticHost()
    {
        return $this->staticHost;
    }
}
