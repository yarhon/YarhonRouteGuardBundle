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
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Yarhon\RouteGuardBundle\DependencyInjection\Container\ClassMap;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadataFactory;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerMetadataFactoryTest extends TestCase
{
    private $controllerNameResolver;

    private $argumentMetadataFactory;

    private $factory;

    public function setUp()
    {
        $this->argumentMetadataFactory = $this->createMock(ArgumentMetadataFactoryInterface::class);

        $classMap = [
            'service1' => 'service1_class',
            'service2' => '\\service2_class',
            'service3' => null,
        ];

        $classMap = new ClassMap($classMap);

        $this->factory = new ControllerMetadataFactory($this->argumentMetadataFactory, $classMap);
    }

    /**
     * @dataProvider createMetadataDataProvider
     */
    public function testCreateMetadata($controllerName, $argumentMetadatas, $expected)
    {
        $this->argumentMetadataFactory->method('createArgumentMetadata')
            ->willReturn($argumentMetadatas);

        $metadata = $this->factory->createMetadata($controllerName);

        $this->assertEquals($expected, $metadata);
    }

    public function createMetadataDataProvider()
    {
        return [
            [
                'class::method',
                [
                    new ArgumentMetadata('arg1', 'int', false, false, null),
                    new ArgumentMetadata('arg2', 'string', false, false, null),
                ],

                new ControllerMetadata('class::method', 'class', 'method', [
                    new ArgumentMetadata('arg1', 'int', false, false, null),
                    new ArgumentMetadata('arg2', 'string', false, false, null),
                ], null),
            ],
            [
                'service1::method',
                [],
                new ControllerMetadata('service1::method', 'service1_class', 'method', [], 'service1'),
            ],
            [
                '\\class::method',
                [],
                new ControllerMetadata('\\class::method', 'class', 'method', [], null),
            ],
            [
                'service2::method',
                [],
                new ControllerMetadata('service2::method', 'service2_class', 'method', [], 'service2'),
            ],
        ];
    }

    public function testCreateMetadataForControllerAsServiceException()
    {
        $this->argumentMetadataFactory->method('createArgumentMetadata')
            ->willReturn([]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to resolve class for service "service3".');

        $this->factory->createMetadata('service3::method');
    }
}
