<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Http;

use Yarhon\RouteGuardBundle\Security\Test\TestBagInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface TestBagMapInterface extends \IteratorAggregate
{
    /**
     * @param TestBagInterface                $testBag
     * @param RequestConstraintInterface|null $requestConstraint
     */
    public function add(TestBagInterface $testBag, RequestConstraintInterface $requestConstraint = null);

}
