<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestBagResolver;

use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestBagInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Security\Http\RequestContextFactory;
use Yarhon\RouteGuardBundle\Security\Http\RequestDependentTestBagInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestBagResolver implements TestBagResolverInterface
{
    /**
     * @var RequestContextFactory
     */
    private $requestContextFactory;

    /**
     * @param RequestContextFactory $requestContextFactory
     */
    public function __construct(RequestContextFactory $requestContextFactory)
    {
        $this->requestContextFactory = $requestContextFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(AbstractTestBagInterface $testBag, RouteContextInterface $routeContext)
    {
        if ($testBag instanceof TestBagInterface) {
            return $testBag->getTests();
        } elseif ($testBag instanceof RequestDependentTestBagInterface) {
            $requestContext = $this->requestContextFactory->createContext($routeContext);

            return $testBag->getTests($requestContext);
        }

        // TODO: throw exception if not supported $testBag passed
    }
}
