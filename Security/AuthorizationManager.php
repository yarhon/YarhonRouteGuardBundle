<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AuthorizationManager implements AuthorizationManagerInterface
{
    /**
     * @var AccessMap
     */
    private $accessMap;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(AccessMapBuilderInterface $accessMapBuilder, AuthorizationCheckerInterface $authorizationChecker = null)
    {
        $this->accessMap = $accessMapBuilder->build();
        $this->authorizationChecker = $authorizationChecker;
    }

    public function isGranted($routeName, $generatedPath = null)
    {
        // TODO: check that authorizationChecker is passed

        var_dump('isGranted call');

        // get them from access map
        $roles = [];

        // !!! pass a request as a subject
        $subject = null;

        //return $this->authorizationChecker->isGranted($roles, $subject);
    }
}
