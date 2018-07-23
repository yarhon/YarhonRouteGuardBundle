<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Security\Authorization;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AuthorizationManager implements AuthorizationManagerInterface
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        //$this->accessMap = $accessMap;
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
