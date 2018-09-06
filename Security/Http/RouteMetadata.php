<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Http;

use Symfony\Component\Routing\Route;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteMetadata
{
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
        $compiledRoute = $route->compile();

        $this->hasHost = (bool) $route->getHost();
        $this->hasStaticHost = $this->hasHost && !$compiledRoute->getHostVariables();
        $this->staticHost = $this->hasStaticHost ? $route->getHost() : null;
    }

    /**
     * @return bool
     */
    public function hasHost()
    {
        return $this->hasHost;
    }

    /**
     * @return bool
     */
    public function hasStaticHost()
    {
        return $this->hasStaticHost;
    }

    /**
     * @return string|null
     */
    public function getStaticHost()
    {
        return $this->staticHost;
    }
}
