<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Provider;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationException;
use Symfony\Component\Routing\Route;
use Psr\Log\LoggerAwareTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security as SecurityAnnotation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted as IsGrantedAnnotation;
use Yarhon\RouteGuardBundle\Annotations\ClassMethodAnnotationReader;
use Yarhon\RouteGuardBundle\Security\Authorization\Test\TestBag;
use Yarhon\RouteGuardBundle\Security\Authorization\Test\Arguments;
use Yarhon\RouteGuardBundle\ExpressionLanguage\ExpressionFactoryInterface;

/**
 * SensioSecurityProvider processes Security & IsGranted annotations of Sensio FrameworkExtraBundle.
 *
 * @see https://symfony.com/doc/5.0/bundles/SensioFrameworkExtraBundle/annotations/security.html
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioSecurityProvider implements ProviderInterface
{
    use LoggerAwareTrait;

    /**
     * @var ClassMethodAnnotationReader
     */
    private $reader;

    /**
     * @var ExpressionFactoryInterface
     */
    private $expressionFactory;

    /**
     * SensioSecurityProvider constructor.
     *
     * @param ExpressionFactoryInterface $expressionFactory
     * @param Reader|null                $reader
     *
     * @throws AnnotationException
     */
    public function __construct(ExpressionFactoryInterface $expressionFactory, Reader $reader = null)
    {
        $this->expressionFactory = $expressionFactory;

        if (null === $reader) {
            // TODO: use CachedReader ?
            $reader = new AnnotationReader();
        }

        $this->reader = new ClassMethodAnnotationReader($reader);

        $this->reader->addAnnotationClass(SecurityAnnotation::class);
        $this->reader->addAnnotationClass(IsGrantedAnnotation::class);
    }

    public function onBuild()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getTests(Route $route)
    {
        $controller = $route->getDefault('_controller');
        if (!$controller && !is_string($controller)) {
            return null;
        }

        list($class, $method) = explode('::', $controller);

        $annotations = $this->reader->read($class, $method);

        $tests = [];

        foreach ($annotations as $annotation) {
            $attributes = [];
            $subject = null;

            if ($annotation instanceof SecurityAnnotation) {
                // TODO: !!! check how sensio expressions differ from access_control expressions
                $expression = $annotation->getExpression();
                $expression = $this->expressionFactory->create($expression);
                $attributes[] = $expression;
            } elseif ($annotation instanceof IsGrantedAnnotation) {
                // Despite of the name, $annotation->getAttributes() is a string (annotation value)
                $attributes[] = $annotation->getAttributes();
                $subject = $annotation->getSubject();
            }

            $arguments = new Arguments($attributes);
            if ($subject) {
                $arguments->setSubjectMetadata(Arguments::SUBJECT_CONTROLLER_ARGUMENT, $subject);
            }
            $tests[] = $arguments;
        }

        if (count($tests)) {
            return new TestBag($tests);
        }

        return null;
    }
}
