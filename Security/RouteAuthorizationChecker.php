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
use Yarhon\RouteGuardBundle\Security\AuthorizationChecker\AuthorizationCheckerInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Security\TestResolver\TestResolverInterface;

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

    /**
     * @param TestLoaderInterface           $testLoader
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(TestLoaderInterface $testLoader, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->testLoader = $testLoader;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(RouteContextInterface $routeContext)
    {
        $tests = $this->testLoader->load($routeContext);

        foreach ($tests as $test) {
            if (!$this->authorizationChecker->isGranted($test, $routeContext)) {
                return false;
            }
        }

        return true;
    }
}
