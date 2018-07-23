<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Security\Provider;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationException;
use Symfony\Component\Routing\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security as SecurityAnnotation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted as IsGrantedAnnotation;
use Yarhon\LinkGuardBundle\Annotations\ClassMethodAnnotationReader;
use Yarhon\LinkGuardBundle\Security\Authorization\Test\TestBag;
use Yarhon\LinkGuardBundle\Security\Authorization\Test\Arguments;

/**
 * SensioSecurityProvider processes Security & IsGranted annotations of Sensio FrameworkExtraBundle.
 *
 * @see https://symfony.com/doc/5.0/bundles/SensioFrameworkExtraBundle/annotations/security.html
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioSecurityProvider implements ProviderInterface
{
    /**
     * @var ClassMethodAnnotationReader
     */
    private $reader;

    /**
     * SensioSecurityProvider constructor.
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

        $this->reader = new ClassMethodAnnotationReader($reader);

        $this->reader->addAnnotationClass(SecurityAnnotation::class);
        $this->reader->addAnnotationClass(IsGrantedAnnotation::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getTests(Route $route)
    {
        $controller = $route->getDefault('_controller');
        if (!$controller && !is_string($controller)) {
            return [];
        }

        list($class, $method) = explode('::', $controller);

        $annotations = $this->reader->read($class, $method);

        $tests = [];

        foreach ($annotations as $annotation) {
            $arguments = new Arguments();
            $tests[] = $arguments;

            if ($annotation instanceof SecurityAnnotation) {
                // TODO: !!! check how sensio expressions differ from access_control expressions
                $expression = $annotation->getExpression();
                $arguments->addAttribute($expression);
            } elseif ($annotation instanceof IsGrantedAnnotation) {
                // Despite of the name, $annotation->getAttributes() is a string (annotation value)
                $arguments->addAttribute($annotation->getAttributes());

                if ($annotation->getSubject()) {
                    $arguments->setSubjectMetadata(Arguments::SUBJECT_CONTROLLER_ARGUMENT, $annotation->getSubject());
                }
            }
        }

        $testBag = new TestBag($tests);

        return $testBag;
    }
}
