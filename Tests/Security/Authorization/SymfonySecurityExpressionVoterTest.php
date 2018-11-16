<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\Authorization;

use PHPUnit\Framework\TestCase;
use Yarhon\RouteGuardBundle\Security\Authorization\SymfonySecurityExpressionVoter;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonySecurityExpressionVoterTest extends TestCase
{
    public function testGetVariableNames()
    {
        $expected = [
            'token',
            'user',
            'object',
            'subject',
            'roles',
            'trust_resolver',
            'request',
        ];

        $this->assertSame($expected, SymfonySecurityExpressionVoter::getVariableNames());
    }

    public function testSetVariableNames()
    {
        $default = SymfonySecurityExpressionVoter::getVariableNames();

        $new = [
            'foo',
            'bar',
        ];

        SymfonySecurityExpressionVoter::setVariableNames($new);

        $this->assertSame($new, SymfonySecurityExpressionVoter::getVariableNames());

        SymfonySecurityExpressionVoter::setVariableNames($default);
    }
}
