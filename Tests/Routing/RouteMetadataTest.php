<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Routing;

use PHPUnit\Framework\TestCase;
use Yarhon\RouteGuardBundle\Routing\RouteMetadata;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteMetadataTest extends TestCase
{
    public function testGeneral()
    {
        $routeMetadata = new RouteMetadata(['page' => 1], ['page', 'offset']);

        $this->assertEquals(['page' => 1], $routeMetadata->getDefaults());
        $this->assertEquals(['page', 'offset'], $routeMetadata->getVariables());
    }
}
