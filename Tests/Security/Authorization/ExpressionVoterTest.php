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
use Yarhon\RouteGuardBundle\Security\Authorization\ExpressionVoter;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ExpressionVoterTest extends TestCase
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

        $this->assertSame($expected, ExpressionVoter::getVariableNames());
    }

    public function testAddVariableNames()
    {
        $expected = [
            'token',
            'user',
            'object',
            'subject',
            'roles',
            'trust_resolver',
            'request',
            'new'
        ];

        ExpressionVoter::addVariableNames(['new']);

        $this->assertSame($expected, ExpressionVoter::getVariableNames());
    }
}