<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Yarhon\RouteGuardBundle\Security\AccessMap;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapTest extends TestCase
{
    private $cache;

    private $accessMap;

    public function setUp()
    {
        $this->cache = $this->createMock(CacheItemPoolInterface::class);
        $this->accessMap = new AccessMap($this->cache);
    }

}
