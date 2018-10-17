<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Http;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RegexParser
{
    private static $metaCharacters = '\\.+*?|^$[](){}';

    private static $removePreviousMetaCharacters = '+*?{|';

    /**
     * @param string $expression
     *
     * @return array
     */
    public function parse($expression)
    {
        $hasStringStartAssert = false;
        $hasStringEndAssert = false;
        $dynamicPartIsWildcard = false;

        if ('^' === $expression[0]) {
            $hasStringStartAssert = true;
            $expression = substr($expression, 1);
        }

        $lastIndex = strlen($expression) - 1;
        if ('$' === $expression[$lastIndex] && (!isset($expression[$lastIndex - 1]) || '\\' !== $expression[$lastIndex - 1])) {
            $hasStringEndAssert = true;
            $expression = substr($expression, 0, -1);
        }

        list($staticPrefix, $dynamicPart) = $this->parseStaticPrefix($expression);

        if (('' === $dynamicPart && !$hasStringEndAssert) || '.*' === $dynamicPart) {
            $dynamicPartIsWildcard = true;
        }

        return [
            'hasStringStartAssert' => $hasStringStartAssert,
            'hasStringEndAssert' => $hasStringEndAssert,
            'staticPrefix' => $staticPrefix,
            'dynamicPartIsWildcard' => $dynamicPartIsWildcard,
        ];
    }

    /**
     * @param string $expression
     *
     * @return array
     */
    private function parseStaticPrefix($expression)
    {
        $prefix = '';

        for ($i = 0; $i < strlen($expression); $i++) {
            $symbol = $expression[$i];

            if (isset($expression[$i + 1])) {
                $nextSymbol = $expression[$i + 1];

                if ('\\' === $symbol) {
                    if (false !== strpos(self::$metaCharacters, $nextSymbol)) {
                        // Escaped meta character symbol, i.e. "."
                        $prefix .= $nextSymbol;
                        $i++;
                        continue;
                    }

                    // Some special structure, like non-printing character / character type / assertion
                    break;
                }

                if (false !== strpos(self::$removePreviousMetaCharacters, $nextSymbol)) {
                    // Quantifier or alternative branch
                    break;
                }
            }

            if (false !== strpos(self::$metaCharacters, $symbol)) {
                break;
            }

            $prefix .= $symbol;
        }

        $dynamicPart = substr($expression, $i);

        return [$prefix, $dynamicPart];
    }
}
