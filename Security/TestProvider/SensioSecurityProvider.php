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
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security as SecurityAnnotation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted as IsGrantedAnnotation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter as ParamConverterAnnotation;
use Yarhon\RouteGuardBundle\Annotations\ClassMethodAnnotationReaderInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Security\Sensio\ExpressionDecorator;
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
    private $annotationReader;

    /**
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @var array
     */
    private $testArguments = [];

    /**
     * SensioSecurityProvider constructor.
     *
     * @param ClassMethodAnnotationReaderInterface $annotationReader
     */
    public function __construct(ClassMethodAnnotationReaderInterface $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @param ExpressionLanguage $expressionLanguage
     */
    public function setExpressionLanguage(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    /**
     * {@inheritdoc}
     */
    public function getTests($routeName, Route $route, ControllerMetadata $controllerMetadata = null)
    {
        if (!$controllerMetadata) {
            return null;
        }

        list($class, $method) = explode('::', $controllerMetadata->getName());
        $annotations = $this->annotationReader->read($class, $method, [SecurityAnnotation::class, IsGrantedAnnotation::class]);

        if (!count($annotations)) {
            return null;
        }

        // $variableNames = $this->getVariableNames($controllerMetadata);

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
                    // At first try to create expression without any variable names to save time during expression resolving
                    $expression = $this->createExpression($expression);
                } catch (InvalidArgumentException $e) {
                    $expression = $this->createExpression($expression, $variableNames);
                }

                $attributes[] = $expression;
            } elseif ($annotation instanceof IsGrantedAnnotation) {
                // Despite of the name, $annotation->getAttributes() is a string (annotation value)
                $attributes[] = $annotation->getAttributes();

                $subjectName = $annotation->getSubject() ?: null;

                if ($subjectName && !in_array($subjectName, $variableNames, true)) {
                    throw new InvalidArgumentException(sprintf('Unknown subject variable "%s". Known variables: "%s".', $subjectName, implode('", "', $variableNames)));
                }
            }

            $arguments = $this->createTestArguments($attributes, $subjectName);

            $tests[] = $arguments;
        }

        return new TestBag($tests);
    }

    private function getVariableNames(ControllerMetadata $controllerMetadata = null)
    {
        $names = [];

        if (null !== $controllerMetadata) {
            $names = array_merge($names, $controllerMetadata->keys());
        }

        return $names;
    }

    /**
     * @param string $expression
     * @param array  $variableNames
     *
     * @return ExpressionDecorator
     *
     * @throws InvalidArgumentException
     */
    private function createExpression($expression, array $variableNames = [])
    {
        $voterVariableNames = SensioSecurityExpressionVoter::getVariableNames();
        $namesToParse = array_merge($voterVariableNames, $variableNames);

        // TODO: warning if some variable names overlaps with SensioSecurityExpressionVoter variables

        try {
            $parsed = $this->expressionLanguage->parse($expression, $namesToParse);
        } catch (SyntaxError $e) {
            throw new InvalidArgumentException(sprintf('Cannot parse expression "%s" with following variables: "%s".', $expression, implode('", "', $namesToParse)), 0, $e);
        }

        $expression = new ExpressionDecorator($parsed, $variableNames);

        return $expression;
    }

    /**
     * @param array       $attributes
     * @param string|null $subjectName
     *
     * @return TestArguments
     */
    private function createTestArguments(array $attributes, $subjectName)
    {
        $expressionsWithContext = array_filter($attributes, function ($attribute) {
            return $attribute instanceof ExpressionDecorator && 0 !== count($attribute->getNames());
        });

        if (null !== $subjectName || count($expressionsWithContext)) {
            $testArguments = new TestArguments($attributes);
            if (null !== $subjectName) {
                $testArguments->setMetadata($subjectName);
            }

            return $testArguments;
        }

        $roles = $attributes;

        $expressions = array_filter($attributes, function ($attribute) {
            return $attribute instanceof ExpressionDecorator;
        });

        $roles = array_diff($roles, $expressions);

        $expressions = array_map(function ($expression) {
            return (string) $expression;
        }, $expressions);

        $roles = array_unique($roles);
        sort($roles);

        $uniqueKey = implode('#', array_merge($roles, $expressions));

        if (!isset($this->testArguments[$uniqueKey])) {
            $this->testArguments[$uniqueKey] = new TestArguments($attributes);
        }

        return $this->testArguments[$uniqueKey];
    }
}
