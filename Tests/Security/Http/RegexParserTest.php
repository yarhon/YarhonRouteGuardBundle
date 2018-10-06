<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Security\Http;

use PHPUnit\Framework\TestCase;
use Yarhon\RouteGuardBundle\Security\Http\RegexParser;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RegexParserTest extends TestCase
{
    private $parser;

    public function setUp()
    {
        $this->parser = new RegexParser();
    }

    /**
     * @dataProvider parseDataProvider
     */
    public function testParse($pattern, $expected)
    {
        $expected = array_combine([
            'hasStringStartAssert',
            'hasStringEndAssert',
            'staticPrefix',
        ], $expected);

        $this->assertEquals($expected, $this->parser->parse($pattern));
    }

    public function parseDataProvider()
    {
        return [
            ['^foo$', [true, true, 'foo']],
            ['^foo\\$', [true, false, 'foo$']],
            [ 'foo', [false, false, 'foo']],
            [ '\\\\', [false, false, '\\']],
            [ '\\.\\+\\*\\?\\|\\^\\$\\[\\]\\(\\)\\{\\}', [false, false, '.+*?|^$[](){}']],
            [ 'foo(bar)', [false, false, 'foo']],
            [ 'foo[bar]', [false, false, 'foo']],
            [ 'foo.', [false, false, 'foo']],
            [ 'foo\\bar', [false, false, 'foo']],
            [ 'foob{1}', [false, false, 'foo']],
            [ 'foob+', [false, false, 'foo']],
            [ 'foob*', [false, false, 'foo']],
            [ 'foob?', [false, false, 'foo']],
            [ 'foob|c', [false, false, 'foo']],
            [ 'foo$a', [false, false, 'foo']],
            [ 'foo^a', [false, false, 'foo']],
        ];
    }
}
