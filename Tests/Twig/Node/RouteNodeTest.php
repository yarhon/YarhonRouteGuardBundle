<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Twig\Node;

use Twig\Node\Node;
use Twig\Error\SyntaxError;
use Twig\Error\RuntimeError;
use Yarhon\LinkGuardBundle\Tests\Twig\AbstractNodeTest;
use Yarhon\LinkGuardBundle\Twig\Node\RouteNode;
use Yarhon\LinkGuardBundle\Twig\Node\RouteExpression;

class RouteNodeTest extends AbstractNodeTest
{
    private $referenceVarName = 'ref';

    /**
     * @dataProvider compileDataProvider
     */
    public function testCompile($source, $expected)
    {
        $node = $this->parse($source);
        $source = $this->compile($node);

        $conditionNode = $node->getNode('condition');
        $conditionSource = $this->compile($conditionNode);
        $expected = sprintf($expected, $this->referenceVarName, $conditionSource, $this->referenceVarName);

        $this->assertEquals($expected, $source);
    }

    public function compileDataProvider()
    {
        $dataSet = [];

        // general test
        $dataSet[0][0] = '{% $tagName ["secure1"] %}body text{% end$tagName %}';
        $dataSet[0][1] = <<<'EOD'
if (false !== ($context["%s"] = %s)) {
    echo "body text";
}
unset($context["%s"]);

EOD;
        // else node test
        $dataSet[1][] = '{% $tagName ["secure1"] %}body text{% else %}else text{% end$tagName %}';
        $dataSet[1][] = <<<'EOD'
if (false !== ($context["%s"] = %s)) {
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

    public function testSetReferenceVarName()
    {
        $testName = uniqid();
        RouteNode::setReferenceVarName($testName);

        $this->assertAttributeEquals($testName, 'referenceVarName', RouteNode::class);
    }

    public function testSetReferenceVarNameException()
    {
        RouteNode::setReferenceVarName(null);

        $routeExpression = $this->createMock(RouteExpression::class);
        $node = new RouteNode($routeExpression, new Node());

        $this->expectException(RuntimeError::class);
        $this->expectExceptionMessage('referenceVarName is not set. setReferenceVarName() method should be called before compiling.');

        $this->compile($node);
    }
}
