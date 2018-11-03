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
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;
use Yarhon\RouteGuardBundle\Security\Test\IsGrantedTest;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteAuthorizationChecker implements RouteAuthorizationCheckerInterface
{
    /**
     * @var TestLoaderInterface
     */
    private $testLoader;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

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
            if ($test instanceof IsGrantedTest) {
                $result = $this->authorizationChecker->isGranted($test->getAttributes(), $test->getSubject());

                if (!$result) {
                    return false;
                }
            }
        }

        return true;
    }
}
