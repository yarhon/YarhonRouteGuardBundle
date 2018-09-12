<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\Sensio;

use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\Expression;
use Yarhon\RouteGuardBundle\Security\Sensio\ExpressionDecorator;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @group requires-expression-language
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ExpressionDecoratorTest extends TestCase
{
    private $expression;

    public function setUp()
    {
        $this->expression = $this->createMock(Expression::class);
    }

    public function testConstruct()
    {
        $expressionDecorator = new ExpressionDecorator($this->expression, ['foo']);

        $this->assertSame($this->expression, $expressionDecorator->getExpression());
        $this->assertSame(['foo'], $expressionDecorator->getNames());
    }

    public function testVariables()
    {
        $expressionDecorator = new ExpressionDecorator($this->expression, ['foo']);

        $expressionDecorator->setVariables(['foo' => 1]);

        $this->assertSame(['foo' => 1], $expressionDecorator->getVariables());
    }

    public function testVariablesMissedException()
    {
        $expressionDecorator = new ExpressionDecorator($this->expression, ['foo', 'bar']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missed variables: "foo", "bar".');

        $expressionDecorator->setVariables([]);
    }

    public function testVariablesUnknownException()
    {
        $expressionDecorator = new ExpressionDecorator($this->expression, ['foo']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown variables: "foo1", "bar".');

        $expressionDecorator->setVariables(['foo' => 1, 'foo1' => 2, 'bar' => 3]);
    }
}
