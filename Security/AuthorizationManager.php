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
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AuthorizationManager implements AuthorizationManagerInterface
{
    /**
     * @var AccessMapResolver
     */
    private $accessMapResolver;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(AccessMapResolver $accessMapResolver, AuthorizationCheckerInterface $authorizationChecker = null)
    {
        $this->accessMapResolver = $accessMapResolver;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function isGranted(RouteContextInterface $routeContext)
    {
        // TODO: check that authorizationChecker is passed

        $tests = $this->accessMapResolver->getTests($routeContext);

        foreach ($tests as $testArguments) {
            /** @var TestArguments $testArguments */
            $result = $this->authorizationChecker->isGranted($testArguments->getAttributes(), $testArguments->getSubject());

            if (!$result) {
                return false;
            }
        }

        return true;
    }
}
