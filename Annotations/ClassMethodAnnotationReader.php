<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Annotations;

use Doctrine\Common\Annotations\Reader;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ClassMethodAnnotationReader implements ClassMethodAnnotationReaderInterface
{
    /**
     * @var Reader
     */
    private $delegate;

    /**
     * @var string[]
     */
    private $annotationClasses;

    /**
     * AnnotationParser constructor.
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->delegate = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function addAnnotationClass($annotationClass)
    {
        $this->annotationClasses[] = $annotationClass;
    }

    /**
     * Note: Method annotation(s) doesn't replaces class annotation(s), but they are merged.
     *
     * {@inheritdoc}
     */
    public function read($class, $method)
    {
        $object = new \ReflectionClass($class);
        $method = $object->getMethod($method);

        $classAnnotations = $this->filter($this->delegate->getClassAnnotations($object));
        $methodAnnotations = $this->filter($this->delegate->getMethodAnnotations($method));

        $annotations = array_merge($classAnnotations, $methodAnnotations);

        return $annotations;
    }

    /**
     * @param array $annotations
     *
     * @return array Filtered annotations
     */
    private function filter(array $annotations)
    {
        $filtered = [];

        foreach ($annotations as $annotation) {
            if (false === array_search(get_class($annotation), $this->annotationClasses, true)) {
                continue;
            }

            $filtered[] = $annotation;
        }

        return $filtered;
    }
}
