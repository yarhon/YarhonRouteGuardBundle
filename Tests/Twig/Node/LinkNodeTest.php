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
use Yarhon\LinkGuardBundle\Twig\Node\LinkNode;
use Yarhon\LinkGuardBundle\Twig\Node\RouteExpression;

class LinkNodeTest extends AbstractNodeTest
{
    private $referenceVarName = 'route_reference';

    /**
     * @dataProvider compileDataProvider
     */
    public function testCompile($source, $expected)
    {
        $node = $this->parse($source);
        $source = $this->compile($node);

        $conditionNode = $node->getNode('condition');
        $conditionSource = $this->compile($conditionNode);
        $expected = sprintf($expected, $this->referenceVarName, $conditionSource);

        $this->assertEquals($expected, $source);
    }

    public function compileDataProvider()
    {
        $dataSet = [];

        // general test
        $dataSet[0][0] = '{% $linkTag ["secure1"] %}body text{% end$linkTag %}';
        $dataSet[0][1] = <<<'EOD'
if (false !== ($context["%s"] = %s)) {
    echo "body text";
}

EOD;
        // else node test
        $dataSet[1][] = '{% $linkTag ["secure1"] %}body text{% else %}else text{% end$linkTag %}';
        $dataSet[1][] = <<<'EOD'
if (false !== ($context["%s"] = %s)) {
    echo "body text";
} else {
    echo "else text";
}

EOD;

        return $dataSet;
    }

    public function testCompileWithoutConditionException()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Condition node is required.');

        $node = new LinkNode(null, new Node());
        $this->compile($node);
    }

    public function testSetReferenceVarName()
    {
        $testName = uniqid();
        LinkNode::setReferenceVarName($testName);

        $this->assertAttributeEquals($testName, 'referenceVarName', LinkNode::class);
    }

    public function testSetReferenceVarNameException()
    {
        LinkNode::setReferenceVarName(null);

        $routeExpression = $this->createMock(RouteExpression::class);
        $node = new LinkNode($routeExpression, new Node());

        $this->expectException(RuntimeError::class);
        $this->expectExceptionMessage('referenceVarName is not set. setReferenceVarName() method should be called before compiling.');

        $this->compile($node);
    }
}
