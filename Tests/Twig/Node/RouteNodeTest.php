<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Twig\Node;

use Twig\Node\Node;
use Twig\Error\SyntaxError;
use Twig\Error\RuntimeError;
use Yarhon\RouteGuardBundle\Tests\Twig\AbstractNodeTest;
use Yarhon\RouteGuardBundle\Twig\Node\RouteNode;
use Yarhon\RouteGuardBundle\Twig\Node\RouteExpression;

class RouteNodeTest extends AbstractNodeTest
{
    private $tagVariableName = '_route';

    /**
     * @dataProvider compileDataProvider
     */
    public function testCompile($source, $expected)
    {
        $node = $this->parse($source);
        $source = $this->compile($node);

        $conditionNode = $node->getNode('condition');
        $conditionSource = $this->compile($conditionNode);
        $expected = sprintf($expected, $this->tagVariableName, $this->tagVariableName, $conditionSource, $this->tagVariableName);

        $this->assertEquals($expected, $source);
    }

    public function compileDataProvider()
    {
        $dataSet = [];

        // general test
        $dataSet[0][0] = '{% $tagName "secure1" %}body text{% end$tagName %}';
        $dataSet[0][1] = <<<'EOD'
$context["%s"] = array();
if (false !== ($context["%s"]["ref"] = %s)) {
    echo "body text";
}
unset($context["%s"]);

EOD;
        // else node test
        $dataSet[1][] = '{% $tagName "secure1" %}body text{% else %}else text{% end$tagName %}';
        $dataSet[1][] = <<<'EOD'
$context["%s"] = array();
if (false !== ($context["%s"]["ref"] = %s)) {
    echo "body text";
} else {
    echo "else text";
}
unset($context["%s"]);

EOD;

        return $dataSet;
    }

    public function testCompileWithoutConditionException()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Condition node is required.');

        $node = new RouteNode(null, new Node());
        $this->compile($node);
    }

    public function testSetVariableName()
    {
        RouteNode::setVariableName('foo');

        $this->assertAttributeEquals('foo', 'variableName', RouteNode::class);
    }

    public function testSetVariableNameException()
    {
        RouteNode::setVariableName(null);

        $routeExpression = $this->createMock(RouteExpression::class);
        $node = new RouteNode($routeExpression, new Node());

        $this->expectException(RuntimeError::class);
        $this->expectExceptionMessage('variableName is not set. setVariableName() method should be called before compiling.');

        $this->compile($node);
    }
}
