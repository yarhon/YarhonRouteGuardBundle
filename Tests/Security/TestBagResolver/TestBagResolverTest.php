<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\TestBagResolver;

use PHPUnit\Framework\TestCase;
use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestBagInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Security\Http\RequestContextFactory;
use Yarhon\RouteGuardBundle\Security\Http\RequestDependentTestBagInterface;
use Yarhon\RouteGuardBundle\Security\TestBagResolver\TestBagResolver;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestBagResolverTest extends TestCase
{
    private $requestContextFactory;

    private $resolver;

    public function setUp()
    {
        $this->requestContextFactory = $this->createMock(RequestContextFactory::class);

        $this->resolver = new TestBagResolver($this->requestContextFactory);
    }
}
