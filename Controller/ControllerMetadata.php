<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Controller;

use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerMetadata
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    /**
     * @var ArgumentMetadata[]
     */
    private $arguments = [];

    /**
     * @var string|null
     */
    private $serviceId;

    /**
     * ControllerMetadata constructor.
     *
     * @param string             $name
     * @param string             $class
     * @param string             $method
     * @param ArgumentMetadata[] $arguments
     * @param string|null        $serviceId
     */
    public function __construct($name, $class, $method, array $arguments = [], $serviceId = null)
    {
        $this->name = $name;
        $this->class = $class;
        $this->method = $method;

        foreach ($arguments as $argument) {
            $this->arguments[$argument->getName()] = $argument;
        }

        $this->serviceId = $serviceId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }


    /**
     * @return ArgumentMetadata[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function hasArgument($name)
    {
        return isset($this->arguments[$name]);
    }

    /**
     * @param $name
     *
     * @return ArgumentMetadata
     *
     * @throws InvalidArgumentException
     */
    public function getArgument($name)
    {
        if (!isset($this->arguments[$name])) {
            throw new InvalidArgumentException(sprintf('Invalid argument name: "%s"', $name));
        }

        return $this->arguments[$name];
    }

    /**
     * @return string|null
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }
}