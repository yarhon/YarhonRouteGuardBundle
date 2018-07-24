<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Fixtures\Routing;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * This object in intended only to provide RouteCollection, all other methods are implemented just
 * to fit interface requirements.
 */
class SimpleRouter implements RouterInterface
{
    public function getRouteCollection()
    {
        return new RouteCollection();
    }

    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH)
    {
        return null;
    }

    public function setContext(RequestContext $context)
    {
        return null;
    }

    public function getContext()
    {
        return null;
    }

    public function match($pathinfo)
    {
        return null;
    }
}
