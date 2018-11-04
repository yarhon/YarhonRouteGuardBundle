<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Security\TestResolver\TestResolverInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;
use Yarhon\RouteGuardBundle\Security\Test\IsGrantedTest;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteAuthorizationChecker implements RouteAuthorizationCheckerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var TestLoaderInterface
     */
    private $testLoader;

    /**
     * @var TestResolverInterface
     */
    private $testResolver;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(TestLoaderInterface $testLoader, TestResolverInterface $testResolver, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->testLoader = $testLoader;
        $this->testResolver = $testResolver;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(RouteContextInterface $routeContext)
    {
        $tests = $this->testLoader->load($routeContext);

        foreach ($tests as $test) {
            if ($test instanceof IsGrantedTest) {
                $arguments = $this->testResolver->resolve($test, $routeContext);
                // TODO: validate arguments?
                $result = $this->authorizationChecker->isGranted(...$arguments);

                if (!$result) {
                    return false;
                }
            }
        }

        return true;
    }
}
