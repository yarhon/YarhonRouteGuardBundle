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
            'dynamicPartIsWildcard',
            'staticPrefix',
        ], $expected);

        $this->assertEquals($expected, $this->parser->parse($pattern));
    }

    public function parseDataProvider()
    {
        return [
            ['^foo$', [true, true, false, 'foo']],
            ['^foo\\$', [true, false, true, 'foo$']],
            ['foo', [false, false, true, 'foo']],
            ['\\\\', [false, false, true, '\\']],
            ['\\.\\+\\*\\?\\|\\^\\$\\[\\]\\(\\)\\{\\}\\d+', [false, false, false, '.+*?|^$[](){}']],
            ['foo(bar)', [false, false, false, 'foo']],
            ['foo[bar]', [false, false, false, 'foo']],
            ['foo.', [false, false, false, 'foo']],
            ['foo\\bar', [false, false, false, 'foo']],
            ['foob{1}', [false, false, false, 'foo']],
            ['foob+', [false, false, false, 'foo']],
            ['foob*', [false, false, false, 'foo']],
            ['foob?', [false, false, false, 'foo']],
            ['foob|c', [false, false, false, 'foo']],
            ['foo$a', [false, false, false, 'foo']],
            ['foo^a', [false, false, false, 'foo']],
            ['foo.*$', [false, true, true, 'foo']],
        ];
    }
}
