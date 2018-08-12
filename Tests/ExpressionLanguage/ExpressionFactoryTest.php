<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\ExpressionLanguage;

use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Expression;
use Yarhon\RouteGuardBundle\ExpressionLanguage\ExpressionFactory;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ExpressionFactoryTest extends TestCase
{

    public function setUp()
    {

    }

    public function testCreate()
    {
        $this->markTestIncomplete();
    }

    public function testCreateException()
    {
        $factory = new ExpressionFactory();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Can\'t create an Expression as ExpressionLanguage is not provided.');

        $factory->create('var == 5', ['var']);
    }

}
