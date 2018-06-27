<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NeonLight\SecureLinksBundle\Security\Authorization;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use NeonLight\SecureLinksBundle\Security\AccessMap;

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

    public function __construct(AccessMap $accessMap, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->accessMap = $accessMap;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function isGranted($routeName, $genereatedPath = null)
    {
        // get them from access map
        $roles = [];

        // !!! pass a request as a subject
        $subject = null;

        return $this->authorizationChecker->isGranted($roles, $subject);
    }
}
