<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Regex;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class Parser implements ParserInterface
{
    protected static $metaCharacters = '.\\+*?|^$[](){}';

    protected static $removePreviousMetaCharacters = '+*?|{';

    /**
     * {@inheritdoc}
     */
    public function parse($expression)
    {
        $hasStringStartAssert = ('^' === $expression[0]);

        $lastIndex = strlen($expression) - 1;
        $hasStringEndAssert = ('$' === $expression[$lastIndex] && (!isset($expression[$lastIndex - 1]) || '\\' !== $expression[$lastIndex - 1]));


    }

    public function matchSubstring($expression, $substring)
    {
        $staticPrefix = $this->parseStaticPrefix($expression);


    }

    private function parseStaticPrefix($expression)
    {
        $prefix = [];

        if ('^' === $expression[0]) {
            $expression = substr($expression, 1);
        }

        for ($i = 0; $i < strlen($expression); $i++) {
            $symbol = $expression[$i];

            if ('\\' === $symbol) {
                $nextSymbol = $expression[$i + 1];
                if (isset($expression[$i + 1]) && false !== strpos(self::$metaCharacters, $nextSymbol)) {
                    // Escaped meta character symbol, i.e. "."
                    $prefix[] = $expression[$i + 1];
                    $i++;
                    continue;
                }

                // Some special structure, like non-printing character / character type / assertion
                break;
            }

            if (false !== strpos(self::$metaCharacters, $symbol)) {
                if (false !== strpos(self::$removePreviousMetaCharacters, $symbol)) {
                    array_pop($prefix);
                }
                break;
            }

            $prefix[] = $symbol;
        }

        return implode('', $prefix);
    }
}
