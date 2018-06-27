<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NeonLight\SecureLinksBundle\Annotations;

use Doctrine\Common\Annotations\Reader;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class Parser
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var string[]
     */
    private $annotationsToParse;

    /**
     * AnnotationParser constructor.
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param string $alias
     * @param string $annotationClass
     */
    public function addAnnotationToParse($alias, $annotationClass)
    {
        $this->annotationsToParse[$alias] = $annotationClass;
    }

    /**
     * Note: Method annotation(s) doesn't replaces class annotation(s), but they are merged.
     *
     * @param string $class
     * @param string $method
     *
     * @return array Parsed annotations, indexed by an alias
     *
     * @throws \ReflectionException
     */
    public function parse($class, $method)
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
            $alias = array_search(get_class($annotation), $this->annotationsToParse, true);
            if (null === $alias) {
                continue;
            }

            $filtered[$alias][] = $annotation;
        }

        return $filtered;
    }
}
