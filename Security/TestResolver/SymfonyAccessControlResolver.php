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
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;
use Yarhon\RouteGuardBundle\Security\Http\TestBagMapInterface;
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
        if ($testBag instanceof TestBagMapInterface) {
            $requestContext = $this->requestContextFactory->createContext($routeContext);
            $testBag = $testBag->resolve($requestContext);
            if (null === $testBag) {
                return [];
            }
        }

        $tests = [];

        $request = $this->requestStack->getCurrentRequest();

        foreach ($testBag as $testArguments) {
            /* @var TestArguments $testArguments */
            $testArguments->setSubject($request); // See \Symfony\Component\Security\Http\Firewall\AccessListener::handle
            $tests[] = $testArguments;
        }

        return $tests;
    }
}
