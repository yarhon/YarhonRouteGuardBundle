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
use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;
use Yarhon\RouteGuardBundle\Security\Test\TestBagInterface;
use Yarhon\RouteGuardBundle\Security\Http\TestBagMapInterface;
use Yarhon\RouteGuardBundle\Security\Http\TestBagMapResolverInterface;
use Yarhon\RouteGuardBundle\Exception\LogicException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonyAccessControlResolver implements TestResolverInterface
{

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TestBagMapResolverInterface
     */
    private $testBagMapResolver;


    public function __construct(RequestStack $requestStack, TestBagMapResolverInterface $testBagMapResolver)
    {
        $this->requestStack = $requestStack;
        $this->testBagMapResolver = $testBagMapResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getName() {

        return 'symfony_access_control';
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(AbstractTestBagInterface $testBag)
    {
        if (!($testBag instanceof TestBagInterface) || !($testBag instanceof TestBagMapInterface)) {
            throw new LogicException(sprintf('%s expects instance of %s or %s.', __CLASS__, TestBagInterface::class, TestBagMapInterface::class));
        }

        if ($testBag instanceof TestBagMapInterface) {
            $testBag = $this->testBagMapResolver->resolve($testBag, $method, $urlDeferred);
            if (null === $testBag) {
                return [];
            };
        }

        $tests = [];

        $request = $this->requestStack->getCurrentRequest();

        foreach ($testBag as $testArguments) {
            /** @var TestArguments $testArguments */
            $testArguments->setSubject($request); // See \Symfony\Component\Security\Http\Firewall\AccessListener::handle
            $tests[] = $testArguments;
        }

        return $tests;
    }
}
