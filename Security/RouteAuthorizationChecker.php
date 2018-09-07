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
class RouteAuthorizationChecker implements RouteAuthorizationCheckerInterface
{
    /**
     * @var RouteTestResolver
     */
    private $routeTestResolver;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(RouteTestResolver $routeTestResolver, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->routeTestResolver = $routeTestResolver;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(RouteContextInterface $routeContext)
    {
        $tests = $this->routeTestResolver->getTests($routeContext);

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
