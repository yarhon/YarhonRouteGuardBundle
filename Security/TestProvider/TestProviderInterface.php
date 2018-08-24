<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestProvider;

use Symfony\Component\Routing\Route;
use Psr\Log\LoggerAwareInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestBagInterface;
use Yarhon\RouteGuardBundle\Security\Http\TestBagMapInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface TestProviderInterface extends LoggerAwareInterface
{
    /**
     * @param Route $route
     *
     * @return TestBagInterface|TestBagMapInterface|null
     */
    public function getTests(Route $route);

    public function onBuild();
}
