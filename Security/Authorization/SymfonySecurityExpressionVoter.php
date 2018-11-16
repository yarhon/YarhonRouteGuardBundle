<?php
/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Authorization;

/**
 * SymfonySecurityExpressionVoter is responsible for storing information about variables used by
 * \Symfony\Component\Security\Core\Authorization\Voter\ExpressionVoter (there's no way to retrieve it from the original class).
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonySecurityExpressionVoter
{
    protected static $variableNames = [
        'token',
        'user',
        'object',
        'subject',
        'roles',
        'trust_resolver',
        'request', // TODO: this variable is conditionally passed to evaluate
    ];

    /**
     * @return array
     */
    public static function getVariableNames()
    {
        return self::$variableNames;
    }

    /**
     * @param array $names
     */
    public static function setVariableNames(array $names)
    {
        self::$variableNames = $names;
    }
}
