<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security;

use Symfony\Component\HttpFoundation\RequestStack;
use Yarhon\RouteGuardBundle\Routing\UrlDeferredInterface;
use Yarhon\RouteGuardBundle\Security\Http\TestBagMapInterface;
use Yarhon\RouteGuardBundle\Security\Http\RequestContextFactory;
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapManager
{
    /**
     * @var AccessMap
     */
    private $accessMap;

    /**
     * @var RequestContextFactory
     */
    private $requestContextFactory;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(AccessMapBuilderInterface $accessMapBuilder, RequestContextFactory $requestContextFactory, RequestStack $requestStack = null)
    {
        $this->accessMap = $accessMapBuilder->build();
        $this->requestContextFactory = $requestContextFactory;
        $this->requestStack = $requestStack;
    }

    public function getTests($routeName, $method = 'GET', UrlDeferredInterface $urlDeferred = null)
    {
        $tests = [];

        $testBags = $this->accessMap->get($routeName);

        foreach ($testBags as $testBag) {
            if ($testBag instanceof TestBagMapInterface) {
                $testBag = $this->resolveTestBagMap($testBag, $method, $urlDeferred);
                if (null === $testBag) {
                    continue;
                };
            }

            foreach ($testBag as $testArguments) {
                $tests[] = $this->resolveTestArguments($testArguments);
            }
        }

        return $tests;
    }

    private function resolveTestBagMap(TestBagMapInterface $testBagMap, $method, UrlDeferredInterface $urlDeferred = null)
    {
        if (null === $this->requestContextFactory) {
            throw new RuntimeException('Unable to resolve TestBagMapInterface instance because RequestContextFactory service is not provided.');
        }

        if (null === $urlDeferred) {
            throw new RuntimeException('Unable to resolve TestBagMapInterface instance because UrlDeferredInterface parameter is not provided.');
        }

        $requestContext = $this->requestContextFactory->create($urlDeferred, $method);

        return $testBagMap->resolve($requestContext);
    }


    private function resolveTestArguments(TestArguments $testArguments)
    {
        $arguments = [];
        $arguments[] = $testArguments->getAttributes();

        if ($testArguments->requiresSubject()) {
            $arguments[] = $this->resolveSubject(...$testArguments->getSubjectMetadata());
        }

        return $arguments;
    }

    private function resolveSubject($type, $name)
    {
        $subject = null;

        if (TestArguments::SUBJECT_CONTEXT_VARIABLE == $type) {
            $request = $this->requestStack->getCurrentRequest();
            $subject = $request;
        } elseif (TestArguments::SUBJECT_CONTROLLER_ARGUMENT == $type) {
            $subject = $this->resolveControllerArgument($name);
        }

        return $subject;
    }

    private function resolveControllerArgument($name)
    {
        return null;
    }

    private function getNamedArguments()
    {
        $request = $this->requestStack->getCurrentRequest();

        $arguments = $controllerArgumentResolver->getArguments($request, $metadata);

        // For SensioSecurityProvider
        // See \Sensio\Bundle\FrameworkExtraBundle\Request\ArgumentNameConverter::getControllerArguments
        // It adds all Request attributes attributes->all(); as possible variables for subject and variables for expressions.
        // While default Symfony behaviour is to consider Request attributes specified in controller method signature only
        // (via RequestAttributeValueResolver).

        $attributes = $request->attributes->all();
        $arguments += $attributes;
    }
}
