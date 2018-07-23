<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Controller;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Yarhon\LinkGuardBundle\Controller\ControllerNameDeprecationsConverter;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerNameDeprecationsConverterTest extends TestCase
{
    /**
     * @var ControllerNameDeprecationsConverter
     */
    private $converter;

    /**
     * @var MockObject
     */
    private $kernel;

    public function setUp()
    {
        $this->kernel = $this->createMock(Kernel::class);

        $bundle = $this->createMock(Bundle::class);
        $bundle->method('getNamespace')
            ->willReturn('Yarhon\LinkGuardBundle\Tests\Fixtures');

        $this->kernel->method('getBundle')
            ->willReturn($bundle);

        $this->converter = new ControllerNameDeprecationsConverter($this->kernel);
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
                ['zzz'],
                ['zzz'],
            ],
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
                'Yarhon\LinkGuardBundle\Tests\Fixtures\Controller\SimpleController::indexAction',
            ],
        ];
    }
}
