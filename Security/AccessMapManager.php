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

    public function getTests($routeName)
    {
        // TODO: check that requestStack is passed

        var_dump('get tests call');

        $request = $this->requestStack->getCurrentRequest();
        var_dump($request);

         // $testBags = $this->accessMap->get($routeName);
    }
}
