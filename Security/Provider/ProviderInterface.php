<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Provider;

use Symfony\Component\Routing\Route;
use Psr\Log\LoggerAwareInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestBagInterface;
use Yarhon\RouteGuardBundle\Security\Http\RequestResolvableInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface ProviderInterface extends LoggerAwareInterface
{
    /**
     * @param Route $route
     *
     * @return TestBagInterface|RequestResolvableInterface|null
     */
    public function getTests(Route $route);

    public function onBuild();
}
