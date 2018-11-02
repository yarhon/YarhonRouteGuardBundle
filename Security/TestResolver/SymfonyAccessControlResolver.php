<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestResolver;

use Symfony\Component\HttpFoundation\RequestStack;
use Yarhon\RouteGuardBundle\Security\Http\RequestContextFactory;
use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestBagInterface;
use Yarhon\RouteGuardBundle\Security\Test\IsGrantedTest;
use Yarhon\RouteGuardBundle\Security\Http\RequestDependentTestBagInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Security\TestProvider\SymfonyAccessControlProvider;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonyAccessControlResolver implements TestResolverInterface
{
    /**
     * @var RequestContextFactory
     */
    private $requestContextFactory;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack          $requestStack
     * @param RequestContextFactory $requestContextFactory
     */
    public function __construct(RequestStack $requestStack, RequestContextFactory $requestContextFactory)
    {
        $this->requestStack = $requestStack;
        $this->requestContextFactory = $requestContextFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderClass()
    {
        return SymfonyAccessControlProvider::class;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(AbstractTestBagInterface $testBag, RouteContextInterface $routeContext)
    {
        if ($testBag instanceof RequestDependentTestBagInterface) {
            $requestContext = $this->requestContextFactory->createContext($routeContext);
            $tests = $testBag->getTests($requestContext);
        } elseif ($testBag instanceof TestBagInterface) {
            $tests = $testBag->getTests();
        }
        // TODO: throw exception if not supported $testBag passed

        $request = $this->requestStack->getCurrentRequest();

        foreach ($tests as $test) {
            /* @var IsGrantedTest $test */
            $test->setSubject($request); // See \Symfony\Component\Security\Http\Firewall\AccessListener::handle
        }

        return $tests;
    }
}
