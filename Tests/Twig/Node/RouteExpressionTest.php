<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Twig\Node;

use PHPUnit\Framework\TestCase;
use Twig_Error_Syntax as SyntaxError;   // Workaround for PhpStorm to recognise type hints. Namespaced name: Twig\Error\SyntaxError
use Twig\Node\Node;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\NameExpression;
use Yarhon\LinkGuardBundle\Twig\Node\RouteExpression;

class RouteExpressionTest extends TestCase
{
    /**
     * @dataProvider constructorDataProvider
     */
    public function testConstructor($sourceArguments, $expectedArguments)
    {
        $expression = new RouteExpression($sourceArguments);
        $arguments = $expression->getNode('arguments');

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
                    new ConstantExpression(false, 0),
                ]),
            ],

            [
                new Node([
                    new ConstantExpression('secure1', 0),
                    new ArrayExpression([
                        new ConstantExpression('page', 0),
                        new ConstantExpression(10, 0)
                    ], 0),
                ]),
                new Node([
                    new ConstantExpression('secure1', 0),
                    new ArrayExpression([
                        new ConstantExpression('page', 0),
                        new ConstantExpression(10, 0)
                    ], 0),
                    new ConstantExpression('GET', 0),
                    new ConstantExpression(false, 0),
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
                    new ConstantExpression(false, 0),
                ]),
            ]
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
                [SyntaxError::class, 'At least one argument (name) is required.']
            ],
            [
                new Node([
                    new ConstantExpression('secure1', 0),
                    new ArrayExpression([], 0),
                    new ConstantExpression('POST', 0),
                    new ConstantExpression(false, 0),
                ]),
                [SyntaxError::class, 'Unrecognized extra arguments, only 3 (name, parameters, method) allowed.']
            ],
        ];
    }

    public function testSetFunctionName()
    {
        $expression = new RouteExpression(new Node([
            new ConstantExpression('secure1', 0),
        ]));

        $this->assertEquals('path_if_granted', $expression->getAttribute('name'));

        $expression->setFunctionName('path');
        $this->assertEquals('path_if_granted', $expression->getAttribute('name'));

        $expression->setFunctionName('url');
        $this->assertEquals('url_if_granted', $expression->getAttribute('name'));

        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Invalid function name: blabla');

        $expression->setFunctionName('blabla');
    }

    public function testSetRelative()
    {
        $expression = new RouteExpression(new Node([
            new ConstantExpression('secure1', 0),
        ]));

        $relativeNode = $expression->getNode('arguments')->getNode(3);
        $this->assertEquals($relativeNode, new ConstantExpression(false, 0));

        $expression->setRelative(true);

        $relativeNode = $expression->getNode('arguments')->getNode(3);
        $this->assertEquals($relativeNode, new ConstantExpression(true, 0));

        $nameExpression = new NameExpression('rel', 0);
        $expression->setRelative($nameExpression);
        $relativeNode = $expression->getNode('arguments')->getNode(3);
        $this->assertEquals($relativeNode, $nameExpression);
    }

    public function testSetGenerateAs()
    {
        $expression = new RouteExpression(new Node([
            new ConstantExpression('secure1', 0),
        ]));

        $expression->setGenerateAs(['url', true]);

        $this->assertEquals('url_if_granted', $expression->getAttribute('name'));

        $relativeNode = $expression->getNode('arguments')->getNode(3);
        $this->assertEquals($relativeNode, new ConstantExpression(true, 0));

        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('setGenerateAs array parameter must have at least one parameter (functionName).');

        $expression->setGenerateAs([]);
    }
}
