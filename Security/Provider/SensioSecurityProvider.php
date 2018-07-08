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
use Yarhon\LinkGuardBundle\Security\Authorization\ArgumentBag;

/**
 * SensioSecurityProvider processes @Security & @IsGranted annotations of Sensio FrameworkExtraBundle.
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
    public function getRouteRules(Route $route)
    {
        $controller = $route->getDefault('_controller');
        if (!$controller && !is_string($controller)) {
            return [];
        }

        list($class, $method) = explode('::', $controller);

        $annotations = $this->reader->read($class, $method);

        $rules = [];

        foreach ($annotations as $annotation) {

            $rule = new ArgumentBag();
            $rules[] = $rule;

            if ($annotation instanceof SecurityAnnotation) {

                // TODO: !!! check how sensio expressions differ from access_control expressions
                $expression = $annotation->getExpression();
                $rule->addAttribute($expression);
            } elseif ($annotation instanceof IsGrantedAnnotation) {
                // Despite of the name, $annotation->getAttributes() is a string (annotation value)
                $rule->addAttribute($annotation->getAttributes());

                if ($annotation->getSubject()) {
                    $rule->setSubjectMetadata(ArgumentBag::SUBJECT_CONTROLLER_ARGUMENT, $annotation->getSubject());
                }
            }
        }

        return $rules;
    }
}
