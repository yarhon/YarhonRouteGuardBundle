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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Yarhon\RouteGuardBundle\Security\Http\TestBagMapInterface;
use Yarhon\RouteGuardBundle\Security\Http\RequestContext;
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;

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
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(AccessMapBuilderInterface $accessMapBuilder, RequestStack $requestStack = null)
    {
        $this->accessMap = $accessMapBuilder->build();
        $this->requestStack = $requestStack;
    }

    public function getTests($routeName, $parameters = [], $method = 'GET', $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        // TODO: check that requestStack is passed

        $tests = [];
        $requestContext = null;

        $testBags = $this->accessMap->get($routeName);

        foreach ($testBags as $testBag) {
            if ($testBag instanceof TestBagMapInterface) {
                $requestContext = $requestContext ?: $this->createRequestContext($method);
                $testBag = $testBag->resolve($requestContext);
            }

            foreach ($testBag as $testArguments) {
                $tests[] = $this->resolveTestArguments($testArguments);
            }
        }

        return $tests;
    }

    private function createRequestContext($method = 'GET')
    {
        $request = $this->requestStack->getCurrentRequest();
        $host = null;

        return new RequestContext(null, $host, $method, $request->getClientIp());
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
        $namedArguments = [];

        foreach ($metadata as $index => $argumentMetadata) {
            $namedArguments[$argumentMetadata->getName()] = $arguments[$index];
        }

        // For SensioSecurityProvider
        // See \Sensio\Bundle\FrameworkExtraBundle\Request\ArgumentNameConverter::getControllerArguments
        // It adds all Request attributes attributes->all(); as possible variables for subject and variables for expressions.
        // While default Symfony behaviour is to consider Request attributes specified in controller method signature only
        // (via RequestAttributeValueResolver).

        $attributes = $request->attributes->all();
        $namedArguments += $attributes;
    }
}
