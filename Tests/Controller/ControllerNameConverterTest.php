<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerNameConverter;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerNameConverterTest extends TestCase
{
    private $converter;

    private $kernel;

    public function setUp()
    {
        $this->kernel = $this->createMock(Kernel::class);

        $bundle = $this->createMock(BundleInterface::class);

        $bundle->method('getNamespace')
            ->willReturn('Yarhon\RouteGuardBundle\Tests\Fixtures');

        $this->kernel->method('getBundle')
            ->willReturn($bundle);

        $this->converter = new ControllerNameConverter($this->kernel);
    }

    /**
     * @dataProvider convertProvider
     */
    public function testConvert($controller, $expected)
    {
        $converted = $this->converter->convert($controller);

        $this->assertEquals($expected, $converted);
    }

    public function convertProvider()
    {
        return [
            [
                'service::method',
                'service::method',
            ],
            [
                'service:method',
                'service::method',
            ],
            [
                'Bundle:Simple:index',
                'Yarhon\RouteGuardBundle\Tests\Fixtures\Controller\SimpleController::indexAction',
            ],
        ];
    }
}
