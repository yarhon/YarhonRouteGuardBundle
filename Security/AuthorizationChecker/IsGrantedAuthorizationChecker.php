<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\AuthorizationChecker;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface as BaseAuthorizationCheckerInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;
use Yarhon\RouteGuardBundle\Security\Test\IsGrantedTest;
use Yarhon\RouteGuardBundle\Security\TestResolver\TestResolverInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class IsGrantedAuthorizationChecker implements AuthorizationCheckerInterface
{
    /**
     * @var BaseAuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var TestResolverInterface
     */
    private $testResolver;

    /**
     * @param BaseAuthorizationCheckerInterface $authorizationChecker
     * @param TestResolverInterface             $testResolver
     */
    public function __construct(BaseAuthorizationCheckerInterface $authorizationChecker, TestResolverInterface $testResolver)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->testResolver = $testResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(TestInterface $test, RouteContextInterface $routeContext)
    {
        $arguments = $this->testResolver->resolve($test, $routeContext);
        // TODO: validate arguments ?

        return $this->authorizationChecker->isGranted(...$arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TestInterface $test)
    {
        return $test instanceof IsGrantedTest;
    }
}
