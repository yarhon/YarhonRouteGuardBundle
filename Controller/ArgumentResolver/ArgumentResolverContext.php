<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller\ArgumentResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ArgumentResolverContext implements ArgumentResolverContextInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var ParameterBag
     */
    private $attributes;

    /**
     * @var string
     */
    private $controllerName;

    /**
     * ArgumentResolverContext constructor.
     *
     * @param Request      $request
     * @param ParameterBag $attributes
     * @param string       $controllerName
     */
    public function __construct(Request $request, ParameterBag $attributes, $controllerName)
    {
        $this->request = $request;
        $this->attributes = $attributes;
        $this->controllerName = $controllerName;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getControllerName()
    {
        return $this->controllerName;
    }
}
