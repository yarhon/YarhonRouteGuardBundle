<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\DependencyInjection\Container;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yarhon\RouteGuardBundle\DependencyInjection\Container\ClassMapBuilder;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ClassMapBuilderTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
    }

    public function testBuild()
    {
        $this->container->register('test1', 'test_class1')->setPublic(true);
        $this->container->register('test2', 'test_class2')->setPublic(true);
        $this->container->setAlias('test1_alias', 'test1')->setPublic(true);

        $this->container->compile();

        $builder = new ClassMapBuilder();
        $map = $builder->build($this->container);

        $this->assertInternalType('array', $map);

        $expected = [
            'test1' => 'test_class1',
            'test1_alias' => 'test_class1',
            'test2' => 'test_class2',
        ];

        $this->assertArraySubset($expected, $map);
    }
}
