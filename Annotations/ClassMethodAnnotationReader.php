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
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationException;

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
     * ClassMethodAnnotationReader constructor.
     *
     * @param Reader|null $reader
     *
     * @throws AnnotationException
     */
    public function __construct(Reader $reader = null)
    {
        if (null === $reader) {
            // TODO: use CachedReader ?
            $reader = new AnnotationReader();
        }

        $this->delegate = $reader;
    }

    /**
     * Note: Method annotation(s) doesn't replaces class annotation(s), but they are merged.
     *
     * {@inheritdoc}
     */
    public function read($class, $method, array $annotationClasses)
    {
        $object = new \ReflectionClass($class);
        $method = $object->getMethod($method);

        $classAnnotations = $this->filter($this->delegate->getClassAnnotations($object), $annotationClasses);
        $methodAnnotations = $this->filter($this->delegate->getMethodAnnotations($method), $annotationClasses);

        $annotations = array_merge($classAnnotations, $methodAnnotations);

        return $annotations;
    }

    /**
     * @param array $annotations
     * @param array $classes
     *
     * @return array Filtered annotations
     */
    private function filter(array $annotations, array $classes)
    {
        $filtered = [];

        foreach ($annotations as $annotation) {
            if (false === array_search(get_class($annotation), $classes, true)) {
                continue;
            }

            $filtered[] = $annotation;
        }

        return $filtered;
    }
}
