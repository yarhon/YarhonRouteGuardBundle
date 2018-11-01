<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Http;

use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface RequestDependentTestBagInterface extends AbstractTestBagInterface
{
    /**
     * @param RequestContext $requestContext
     *
     * @return TestInterface[]
     */
    public function getTests(RequestContext $requestContext);
}
