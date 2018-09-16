<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\CacheWarmer;

use PHPUnit\Framework\TestCase;
use Yarhon\RouteGuardBundle\CacheWarmer\AccessMapCacheWarmer;
use Yarhon\RouteGuardBundle\Security\AccessMapBuilder;
use Yarhon\RouteGuardBundle\Security\AccessMapInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapCacheWarmerTest extends TestCase
{
    public function testWarmUp()
    {
        $accessMap = $this->createMock(AccessMapInterface::class);

        $builder = $this->createMock(AccessMapBuilder::class);

        $builder->expects($this->once())
            ->method('build')
            ->with($accessMap);

        $cacheWarmer = new AccessMapCacheWarmer($builder, $accessMap);

        $cacheWarmer->warmUp('');
    }

    public function testIsOptional()
    {
        $accessMap = $this->createMock(AccessMapInterface::class);
        $builder = $this->createMock(AccessMapBuilder::class);

        $cacheWarmer = new AccessMapCacheWarmer($builder, $accessMap);

        $this->assertTrue($cacheWarmer->isOptional());
    }
}
