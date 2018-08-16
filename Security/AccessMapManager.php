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

        $testBags = $this->accessMap->get($routeName);

        foreach ($testBags as $testBag) {
            if ($testBag instanceof TestBagMapInterface) {
                $testBag = $this->resolveTestBagMap($testBag);
            }

            foreach ($testBag as $testArguments) {
                $tests[] = $this->resolveTestArguments($testArguments);
            }
        }

        return $tests;
    }

    private function resolveTestBagMap(TestBagMapInterface $testBagMap)
    {
        $request = $this->requestStack->getCurrentRequest();
        $requestContext = new RequestContext();

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
}
