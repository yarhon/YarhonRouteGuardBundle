<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Annotations;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface ControllerAnnotationReaderInterface
{
    /**
     * @param string $alias
     * @param string $annotationClass
     */
    public function addAnnotationToRead($alias, $annotationClass);

    /**
     * @param string $class
     * @param string $method
     *
     * @return array Parsed annotations, indexed by an alias
     *
     * @throws \ReflectionException
     */
    public function read($class, $method);
}
