<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Twig\Node;

use Twig_Error_Syntax as SyntaxError;   // Workaround for PhpStorm to recognise type hints. Namespaced name: Twig\Error\SyntaxError
use Twig\Node\Node;
use Yarhon\LinkGuardBundle\Tests\Twig\AbstractNodeTest;
use Yarhon\LinkGuardBundle\Twig\Node\RouteIfGrantedNode;

class RouteIfGrantedNodeTest extends AbstractNodeTest
{
    /**
     * @dataProvider compileDataProvider
     */
    public function testCompile($source, $expected)
    {
        $node = $this->parse($source);
        $source = $this->compile($node);

        $conditionNode = $node->getNode('condition');
        $conditionSource = $this->compile($conditionNode);
        $expected = sprintf($expected, $conditionSource);

        $this->assertEquals($expected, $source);
    }

    public function compileDataProvider()
    {
        $dataSet = [];

        // general test
        $dataSet[0][0] = '{% routeifgranted ["secure1"] %}body text{% endrouteifgranted %}';
        $dataSet[0][1] = <<<'EOD'
if (false !== ($context["route_reference"] = %s)) {
    echo "body text";
}

EOD;
        // else node test
        $dataSet[1][] = '{% routeifgranted ["secure1"] %}body text{% else %}else text{% endrouteifgranted %}';
        $dataSet[1][] = <<<'EOD'
if (false !== ($context["route_reference"] = %s)) {
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

        $node = new RouteIfGrantedNode(null, new Node());
        $this->compile($node);
    }

    public function testGetReferenceVarName()
    {
        $testName = uniqid();
        RouteIfGrantedNode::setReferenceVarName($testName);

        $this->assertEquals($testName, RouteIfGrantedNode::getReferenceVarName());
    }

    public function testGetReferenceVarNameException()
    {
        RouteIfGrantedNode::setReferenceVarName(null);

        $this->expectException('LogicException');
        $this->expectExceptionMessage('referenceVarName is not set. setReferenceVarName() method should be called first.');

        RouteIfGrantedNode::getReferenceVarName();
    }
}
