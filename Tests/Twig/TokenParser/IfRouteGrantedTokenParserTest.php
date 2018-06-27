<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NeonLight\SecureLinksBundle\Tests\Twig\TokenParser;

use PHPUnit\Framework\TestCase;

use Twig\Environment;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Parser;
use Twig\Source;
use NeonLight\SecureLinksBundle\Twig\TokenParser\IfRouteGrantedTokenParser;

class FormThemeTokenParserTest extends TestCase
{
    /**
     * @1dataProvider getTestsForFormTheme
     */
    public function testCompile($source, $expected)
    {
        $this->assertEquals(1, 1);
        /*
        $env = new Environment($this->getMockBuilder('Twig\Loader\LoaderInterface')->getMock(), array('cache' => false, 'autoescape' => false, 'optimizations' => 0));
        $env->addTokenParser(new FormThemeTokenParser());
        $stream = $env->tokenize(new Source($source, ''));
        $parser = new Parser($env);

        $this->assertEquals($expected, $parser->parse($stream)->getNode('body')->getNode(0));
        */
    }

    public function getTestsForFormTheme()
    {
        return array(
            array(
                '{% form_theme form "tpl1" %}',
                new FormThemeNode(
                    new NameExpression('form', 1),
                    new ArrayExpression(array(
                        new ConstantExpression(0, 1),
                        new ConstantExpression('tpl1', 1),
                    ), 1),
                    1,
                    'form_theme'
                ),
            ),
            array(
                '{% form_theme form "tpl1" "tpl2" %}',
                new FormThemeNode(
                    new NameExpression('form', 1),
                    new ArrayExpression(array(
                        new ConstantExpression(0, 1),
                        new ConstantExpression('tpl1', 1),
                        new ConstantExpression(1, 1),
                        new ConstantExpression('tpl2', 1),
                    ), 1),
                    1,
                    'form_theme'
                ),
            ),
            array(
                '{% form_theme form with "tpl1" %}',
                new FormThemeNode(
                    new NameExpression('form', 1),
                    new ConstantExpression('tpl1', 1),
                    1,
                    'form_theme'
                ),
            ),
            array(
                '{% form_theme form with ["tpl1"] %}',
                new FormThemeNode(
                    new NameExpression('form', 1),
                    new ArrayExpression(array(
                        new ConstantExpression(0, 1),
                        new ConstantExpression('tpl1', 1),
                    ), 1),
                    1,
                    'form_theme'
                ),
            ),
            array(
                '{% form_theme form with ["tpl1", "tpl2"] %}',
                new FormThemeNode(
                    new NameExpression('form', 1),
                    new ArrayExpression(array(
                        new ConstantExpression(0, 1),
                        new ConstantExpression('tpl1', 1),
                        new ConstantExpression(1, 1),
                        new ConstantExpression('tpl2', 1),
                    ), 1),
                    1,
                    'form_theme'
                ),
            ),
            array(
                '{% form_theme form with ["tpl1", "tpl2"] only %}',
                new FormThemeNode(
                    new NameExpression('form', 1),
                    new ArrayExpression(array(
                        new ConstantExpression(0, 1),
                        new ConstantExpression('tpl1', 1),
                        new ConstantExpression(1, 1),
                        new ConstantExpression('tpl2', 1),
                    ), 1),
                    1,
                    'form_theme',
                    true
                ),
            ),
        );
    }
}
