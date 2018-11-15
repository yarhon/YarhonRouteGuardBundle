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
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;
use Yarhon\RouteGuardBundle\ExpressionLanguage\ExpressionAnalyzer;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ExpressionAnalyzerTest extends TestCase
{
    private $expressionLanguage;

    private $analyzer;

    public function setUp()
    {
        $this->expressionLanguage = new ExpressionLanguage();
        $this->analyzer = new ExpressionAnalyzer();
    }

    /**
     * @dataProvider getUsedVariablesDataProvider
     */
    public function testGetUsedVariables($expression, $allowedVariables, $expected)
    {
        $parsedExpression = $this->expressionLanguage->parse($expression, $allowedVariables);

        $usedVariables = $this->analyzer->getUsedVariables($parsedExpression);

        $this->assertEquals($expected, $usedVariables);
    }

    public function getUsedVariablesDataProvider()
    {
        return [
            ['request.getMethod == "GET" and (foo == true or bar == { i: "val" })', ['request', 'foo', 'bar'], ['request', 'foo', 'bar']],
            ['foo == 5 or foo == 6', ['foo'], ['foo']],
            ['request.getMethod(foo)', ['request', 'foo'], ['request', 'foo']],
            ['5 < 4', ['request'], []],
            ['5 < 4', [], []],
        ];
    }

    /**
     * @dataProvider getVariableAttributesCallsDataProvider
     */
    public function testGetVariableAttributesCalls($expression, $allowedVariables, $variable, $expected)
    {
        $parsedExpression = $this->expressionLanguage->parse($expression, $allowedVariables);

        $attributes = $this->analyzer->getVariableAttributesCalls($parsedExpression, $variable);

        $this->assertEquals($expected, $attributes);
    }

    public function getVariableAttributesCallsDataProvider()
    {
        return [
            ['request.getMethod == "GET"', ['request'], 'request', ['getMethod']],
            ['request.getMethod == "GET"', ['request'], 'foo', []],
            ['request.getMethod == "GET" or request.getMethod == "POST"', ['request'], 'request', ['getMethod']],
            ['request == true', ['request'], 'request', []],
            ['request.getFormat(request.getMethod)', ['request'], 'request', ['getFormat', 'getMethod']],
        ];
    }
}
