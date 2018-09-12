<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestProvider;

use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security as SecurityAnnotation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted as IsGrantedAnnotation;
use Yarhon\RouteGuardBundle\Annotations\ClassMethodAnnotationReaderInterface;
use Yarhon\RouteGuardBundle\Security\Sensio\VariableResolver;
use Yarhon\RouteGuardBundle\Security\Sensio\ExpressionDecorator;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Routing\RouteMetadata;
use Yarhon\RouteGuardBundle\Security\Test\TestBag;
use Yarhon\RouteGuardBundle\Security\Test\TestArguments;
use Yarhon\RouteGuardBundle\Security\Authorization\SensioSecurityExpressionVoter;
use Yarhon\RouteGuardBundle\Exception\LogicException;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * SensioSecurityProvider processes Security & IsGranted annotations of Sensio FrameworkExtraBundle.
 *
 * @see https://symfony.com/doc/5.0/bundles/SensioFrameworkExtraBundle/annotations/security.html
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioSecurityProvider implements TestProviderInterface
{
    use LoggerAwareTrait;

    /**
     * @var ClassMethodAnnotationReaderInterface
     */
    private $reader;

    /**
     * @var VariableResolver
     */
    private $variableResolver;

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @var ArgumentMetadataFactoryInterface
     */
    private $argumentMetadataFactory;

    /**
     * SensioSecurityProvider constructor.
     *
     * @param ClassMethodAnnotationReaderInterface  $reader
     * @param VariableResolver                      $variableResolver
     * @param ExpressionLanguage|null               $expressionLanguage
     * @param ArgumentMetadataFactoryInterface|null $argumentMetadataFactory
     */
    public function __construct(ClassMethodAnnotationReaderInterface $reader, VariableResolver $variableResolver, ExpressionLanguage $expressionLanguage = null, ArgumentMetadataFactoryInterface $argumentMetadataFactory = null)
    {
        $this->reader = $reader;
        $this->variableResolver = $variableResolver;
        $this->expressionLanguage = $expressionLanguage;

        $this->argumentMetadataFactory = $argumentMetadataFactory ?: new ArgumentMetadataFactory();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sensio_security';
    }

    /**
     * {@inheritdoc}
     */
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

        $arguments = $this->argumentMetadataFactory->createArgumentMetadata([$class, $method]);
        $routeMetadata = new RouteMetadata($route);
        $controllerMetadata = new ControllerMetadata($arguments);
        $variableNames = $this->variableResolver->getVariableNames($routeMetadata, $controllerMetadata);
        // TODO: warning if some variable names overlaps with SensioSecurityExpressionVoter variables

        $annotations = $this->reader->read($class, $method, [SecurityAnnotation::class, IsGrantedAnnotation::class]);

        $tests = [];

        foreach ($annotations as $annotation) {
            $attributes = [];
            $subjectName = null;

            if ($annotation instanceof SecurityAnnotation) {
                if (!$this->expressionLanguage) {
                    throw new LogicException('Cannot create expression because ExpressionLanguage is not provided.');
                }

                $expression = $annotation->getExpression();

                try {
                    // At first try to create expression without any variable names
                    $expression = $this->createExpression($expression);
                } catch (InvalidArgumentException $e) {
                    $expression = $this->createExpression($expression, $variableNames);
                }

                $attributes[] = $expression;
            } elseif ($annotation instanceof IsGrantedAnnotation) {
                // Despite of the name, $annotation->getAttributes() is a string (annotation value)
                $attributes[] = $annotation->getAttributes();

                $subjectName = $annotation->getSubject();

                if ($subjectName && !in_array($subjectName, $variableNames)) {
                    throw new InvalidArgumentException(sprintf('Unknown subject variable "%s". Known variables: "%s"', $subjectName, implode('", "', $variableNames)));
                }
            }

            $arguments = new TestArguments($attributes);

            if ($subjectName) {
                $arguments->setSubjectMetadata($subjectName);
            }
            $tests[] = $arguments;
        }

        if (!count($tests)) {
            return null;
        }

        $testBag = new TestBag($tests);
        $testBag->setMetadata([$routeMetadata, $controllerMetadata]);

        return $testBag;
    }

    /**
     * @param string $expression
     * @param array  $names
     *
     * @return ExpressionDecorator
     *
     * @throws InvalidArgumentException
     */
    private function createExpression($expression, array $names = [])
    {
        $defaultNames = SensioSecurityExpressionVoter::getVariableNames();
        $names = array_merge($defaultNames, $names);

        try {
            $parsed = $this->expressionLanguage->parse($expression, $names);
        } catch (SyntaxError $e) {
            throw new InvalidArgumentException(sprintf('Cannot parse expression "%s" with following variables: "%s".', $expression, implode('", "', $names)), 0, $e);
        }

        $expression = new ExpressionDecorator($parsed, $names);

        return $expression;
    }
}
