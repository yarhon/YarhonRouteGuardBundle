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
     * @var ArgumentMetadata[]
     */
    private $arguments;

    /**
     * ControllerMetadata constructor.
     *
     * @param string             $name
     * @param ArgumentMetadata[] $arguments
    */
    public function __construct($name, array $arguments = [])
    {
        $this->name = $name;

        foreach ($arguments as $argument) {
            $this->addArgument($argument);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ArgumentMetadata[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    public function hasArgument($name)
    {
        return isset($this->arguments[$name]);
    }

    // TODO: add an exception
    public function getArgument($name)
    {
        return $this->arguments[$name];
    }

    private function addArgument(ArgumentMetadata $argument)
    {
        $this->arguments[$argument->getName()] = $argument;
    }
}
