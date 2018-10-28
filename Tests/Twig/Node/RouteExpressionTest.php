<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Twig\Node;

use PHPUnit\Framework\TestCase;
use Twig\Node\Node;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Error\SyntaxError;
use Yarhon\RouteGuardBundle\Twig\Node\RouteExpression;

class RouteExpressionTest extends TestCase
{
    /**
     * @dataProvider constructorDataProvider
     */
    public function testConstructor($sourceArguments, $expectedArguments)
    {
        $expression = new RouteExpression($sourceArguments);
        $arguments = $expression->getNode('arguments');

        // Remove the "generateAs" node, as it's tested in separate method.
        $arguments->removeNode(3);

        $this->assertEquals($expectedArguments, $arguments);
    }

    public function constructorDataProvider()
    {
        return [
            [
                new Node([
                    new ConstantExpression('secure1', 0),
                ]),
                new Node([
                    new ConstantExpression('secure1', 0),
                    new ArrayExpression([], 0),
                    new ConstantExpression('GET', 0),
                ]),
            ],
            [
                new Node([
                    new ConstantExpression('secure1', 0),
                    new ArrayExpression([
                        new ConstantExpression('page', 0),
                        new ConstantExpression(10, 0),
                    ], 0),
                ]),
                new Node([
                    new ConstantExpression('secure1', 0),
                    new ArrayExpression([
                        new ConstantExpression('page', 0),
                        new ConstantExpression(10, 0),
                    ], 0),
                    new ConstantExpression('GET', 0),
                ]),
            ],
            [
                new Node([
                    new ConstantExpression('secure1', 0),
                    new ArrayExpression([], 0),
                    new ConstantExpression('POST', 0),
                ]),
                new Node([
                    new ConstantExpression('secure1', 0),
                    new ArrayExpression([], 0),
                    new ConstantExpression('POST', 0),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider constructorExceptionDataProvider
     */
    public function testConstructorException($sourceArguments, $expected)
    {
        $this->expectException($expected[0]);
        if (isset($expected[1])) {
            $this->expectExceptionMessage($expected[1]);
        }

        $expression = new RouteExpression($sourceArguments);
    }

    public function constructorExceptionDataProvider()
    {
        return [
            [
                new Node([]),
                [SyntaxError::class, 'At least one argument (name) is required.'],
            ],
            [
                new Node([
                    new ConstantExpression('secure1', 0),
                    new ArrayExpression([], 0),
                    new ConstantExpression('POST', 0),
                    new ConstantExpression(false, 0),
                ]),
                [SyntaxError::class, 'Unrecognized extra arguments, only 3 (name, parameters, method) allowed.'],
            ],
        ];
    }

    public function testDefaultGenerateAs()
    {
        $expression = new RouteExpression(new Node([
            new ConstantExpression('secure1', 0),
        ]));

        $arguments = $expression->getNode('arguments');
        $this->assertTrue($arguments->hasNode(3));
        $generateAsNode = $arguments->getNode(3);

        $expected = new ArrayExpression([
            new ConstantExpression(0, 0),
            new ConstantExpression('path', 0),
            new ConstantExpression(1, 0),
            new ConstantExpression(false, 0),
        ], 0);

        $this->assertEquals($generateAsNode, $expected);
    }

    /**
     * @dataProvider setGenerateAsDataProvider
     */
    public function testSetGenerateAs($generateAs, $expected)
    {
        $expression = new RouteExpression(new Node([
            new ConstantExpression('secure1', 0),
        ]));

        $self = $expression->setGenerateAs(...$generateAs);

        $this->assertSame($expression, $self);

        $arguments = $expression->getNode('arguments');
        $this->assertTrue($arguments->hasNode(3));
        $generateAsNode = $arguments->getNode(3);

        $this->assertEquals($generateAsNode, $expected);
    }

    public function setGenerateAsDataProvider()
    {
        return [
            [
                ['path'],
                new ArrayExpression([
                    new ConstantExpression(0, 0),
                    new ConstantExpression('path', 0),
                    new ConstantExpression(1, 0),
                    new ConstantExpression(false, 0),
                ], 0),
            ],
            [
                ['path', false],
                new ArrayExpression([
                    new ConstantExpression(0, 0),
                    new ConstantExpression('path', 0),
                    new ConstantExpression(1, 0),
                    new ConstantExpression(false, 0),
                ], 0),
            ],
            [
                ['path', true],
                new ArrayExpression([
                    new ConstantExpression(0, 0),
                    new ConstantExpression('path', 0),
                    new ConstantExpression(1, 0),
                    new ConstantExpression(true, 0),
                ], 0),
            ],
            [
                ['url', false],
                new ArrayExpression([
                    new ConstantExpression(0, 0),
                    new ConstantExpression('url', 0),
                    new ConstantExpression(1, 0),
                    new ConstantExpression(false, 0),
                ], 0),
            ],
            [
                ['url', true],
                new ArrayExpression([
                    new ConstantExpression(0, 0),
                    new ConstantExpression('url', 0),
                    new ConstantExpression(1, 0),
                    new ConstantExpression(true, 0),
                ], 0),
            ],
            [
                ['url', new ConstantExpression(5, 0)],
                new ArrayExpression([
                    new ConstantExpression(0, 0),
                    new ConstantExpression('url', 0),
                    new ConstantExpression(1, 0),
                    new ConstantExpression(5, 0),
                ], 0),
            ],
        ];
    }

    /**
     * @dataProvider setGenerateAsExceptionDataProvider
     */
    public function testSetGenerateAsException($generateAs, $expected)
    {
        $expression = new RouteExpression(new Node([
            new ConstantExpression('secure1', 0),
        ]));

        $this->expectException($expected[0]);
        $this->expectExceptionMessage($expected[1]);

        $expression->setGenerateAs(...$generateAs);
    }

    public function setGenerateAsExceptionDataProvider()
    {
        return [
            [
               ['foo'],
               [SyntaxError::class, 'Invalid reference type: foo'],
            ],
        ];
    }
}
