<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\AuthorizationChecker;

use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class DelegatingAuthorizationChecker implements AuthorizationCheckerInterface
{
    /**
     * @var \Traversable|AuthorizationCheckerInterface[]
     */
    private $checkers;

    /**
     * @param \Traversable|AuthorizationCheckerInterface[] $checkers
     */
    public function __construct($checkers = [])
    {
        $this->checkers = $checkers;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(TestInterface $test, RouteContextInterface $routeContext)
    {
        foreach ($this->checkers as $checker) {
            if ($checker->supports($test)) {
                return $checker->isGranted($test, $routeContext);
            }
        }

        throw new RuntimeException(sprintf('No authorization checker exists for test instance of "%s".', get_class($test)));
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TestInterface $test)
    {
        return true;
    }
}
