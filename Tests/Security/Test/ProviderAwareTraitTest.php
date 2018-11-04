<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\Test;

use PHPUnit\Framework\TestCase;
use Yarhon\RouteGuardBundle\Security\Test\ProviderAwareTrait;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ProviderAwareTraitTest extends TestCase
{
    public function testProviderClass()
    {
        $providerAware = $this->getMockForTrait(ProviderAwareTrait::class);

        $providerAware->setProviderClass('foo');

        $this->assertSame('foo', $providerAware->getProviderClass());
    }
}
