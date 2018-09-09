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
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerMetadataTest extends TestCase
{
    private $argumentOne;

    private $argumentTwo;

    private $controllerMetadata;

    public function setUp()
    {
        $this->argumentOne = $this->createMock(ArgumentMetadata::class);

        $this->argumentOne->method('getName')
            ->willReturn('arg1');

        $this->argumentTwo = $this->createMock(ArgumentMetadata::class);

        $this->argumentTwo->method('getName')
            ->willReturn('arg2');

        $this->controllerMetadata = new ControllerMetadata([$this->argumentOne, $this->argumentTwo]);
    }

    public function testAll()
    {
        $this->assertSame([$this->argumentOne, $this->argumentTwo], $this->controllerMetadata->all());
    }

    public function testKeys()
    {
        $this->assertSame(['arg1', 'arg2'], $this->controllerMetadata->keys());
    }

    public function testHas()
    {
        $this->assertTrue($this->controllerMetadata->has('arg1'));
        $this->assertFalse($this->controllerMetadata->has('arg3'));
    }

    public function testGet()
    {
        $this->assertSame($this->argumentOne, $this->controllerMetadata->get('arg1'));
    }

    public function testGetException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid argument name: "arg3"');

        $this->controllerMetadata->get('arg3');
    }
}