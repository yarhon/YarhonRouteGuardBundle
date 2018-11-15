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
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Exception\ExceptionInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface ProviderInterface
{
    /**
     * @param string                  $routeName
     * @param Route                   $route
     * @param ControllerMetadata|null $controllerMetadata
     *
     * @return AbstractTestBagInterface|null
     *
     * @throws ExceptionInterface
     */
    public function getTests($routeName, Route $route, ControllerMetadata $controllerMetadata = null);
}
