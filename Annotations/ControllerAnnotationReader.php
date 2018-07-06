<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Annotations;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\SimpleAnnotationReader;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ControllerAnnotationReader implements ControllerAnnotationReaderInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var string[]
     */
    private $annotationsToRead;

    /**
     * AnnotationParser constructor.
     */
    public function __construct()
    {
        $this->reader = new SimpleAnnotationReader();
        // TODO: use CachedReader ?
        // TODO: use DocParser directly ?
        // TODO: check if AnnotationReader (not simple) will only read needed annotations, without need to filter them
    }

    /**
     * {@inheritdoc}
     */
    public function addAnnotationToRead($alias, $annotationClass)
    {
        $this->annotationsToRead[$alias] = $annotationClass;
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

        $classAnnotations = $this->filterAnnotations($this->reader->getClassAnnotations($object));
        $methodAnnotations = $this->filterAnnotations($this->reader->getMethodAnnotations($method));

        $annotations = array_merge_recursive($classAnnotations, $methodAnnotations);

        return $annotations;
    }

    /**
     * @param array $annotations
     *
     * @return array Filtered annotations, indexed by an alias
     */
    private function filterAnnotations(array $annotations)
    {
        $filtered = [];

        foreach ($annotations as $annotation) {
            $alias = array_search(get_class($annotation), $this->annotationsToRead, true);
            if (null === $alias) {
                continue;
            }

            $filtered[$alias][] = $annotation;
        }

        return $filtered;
    }
}
