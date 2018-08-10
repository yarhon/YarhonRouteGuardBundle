<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Routing\RouteCollection;

use PHPUnit\Framework\TestCase;
use Yarhon\RouteGuardBundle\Tests\HelperTrait;
use Yarhon\RouteGuardBundle\Routing\RouteCollection\RemoveIgnoredTransformer;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RemoveIgnoredTransformerTest extends TestCase
{
    use HelperTrait;

    public function testTransform()
    {
        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
            '/path2' => 'class2::method',
            '/path3' => 'extra_class::method',
            '/path4' => false,
        ]);

        $ignoredControllers = [
            'class2',
            'extra',
        ];

        $transformer = new RemoveIgnoredTransformer($ignoredControllers);
        $transformed = $transformer->transform($routeCollection);

        $expected = $this->createRouteCollection([
            '/path1' => 'class::method',
            '/path4' => false,
        ]);

        $this->assertEquals($expected, $transformed);
    }
}
