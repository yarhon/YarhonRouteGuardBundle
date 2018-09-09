<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Twig;

use PHPUnit\Framework\TestCase;
use Yarhon\RouteGuardBundle\Twig\RoutingRuntime;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Yarhon\RouteGuardBundle\Routing\AuthorizedUrlGeneratorInterface;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RoutingRuntimeTest extends TestCase
{
    private $generator;

    private $runtime;

    public function setUp()
    {
        $this->generator = $this->createMock(AuthorizedUrlGeneratorInterface::class);

        $this->runtime = new RoutingRuntime($this->generator);
    }

    public function testUrl()
    {
        $this->generator->expects($this->at(0))
            ->method('generate')
            ->with('route1', ['page' => 1], 'POST', UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('/url1');

        $this->generator->expects($this->at(1))
            ->method('generate')
            ->with('route1', ['page' => 1], 'POST', UrlGeneratorInterface::NETWORK_PATH)
            ->willReturn('/url2');


        $this->assertEquals('/url1', $this->runtime->url('route1', ['page' => 1], 'POST'));
        $this->assertEquals('/url2', $this->runtime->url('route1', ['page' => 1], 'POST', true));
    }

    public function testPath()
    {
        $this->generator->expects($this->at(0))
            ->method('generate')
            ->with('route1', ['page' => 1], 'POST', UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn('/url1');

        $this->generator->expects($this->at(1))
            ->method('generate')
            ->with('route1', ['page' => 1], 'POST', UrlGeneratorInterface::RELATIVE_PATH)
            ->willReturn('/url2');


        $this->assertEquals('/url1', $this->runtime->path('route1', ['page' => 1], 'POST'));
        $this->assertEquals('/url2', $this->runtime->path('route1', ['page' => 1], 'POST', true));
    }


}
