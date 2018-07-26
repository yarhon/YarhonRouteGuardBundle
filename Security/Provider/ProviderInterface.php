<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Security\Provider;

use Symfony\Component\Routing\Route;
use Yarhon\LinkGuardBundle\Security\Authorization\Test\TestBagInterface;
use Yarhon\LinkGuardBundle\Security\Http\RequestResolvableInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface ProviderInterface
{
    /**
     * @param Route $route
     *
     * @return TestBagInterface|RequestResolvableInterface|null
     */
    public function getTests(Route $route);
}
