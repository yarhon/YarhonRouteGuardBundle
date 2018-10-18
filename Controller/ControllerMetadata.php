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
     * @var ArgumentMetadata[]
     */
    private $arguments = [];

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
    public function all()
    {
        return array_values($this->arguments);
    }

    /**
     * @return string[]
     */
    public function keys()
    {
        return array_keys($this->arguments);
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function has($name)
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
    public function get($name)
    {
        if (!isset($this->arguments[$name])) {
            throw new InvalidArgumentException(sprintf('Invalid argument name: "%s"', $name));
        }

        return $this->arguments[$name];
    }

    /**
     * @param ArgumentMetadata $argument
     */
    private function addArgument(ArgumentMetadata $argument)
    {
        $this->arguments[$argument->getName()] = $argument;
    }
}
