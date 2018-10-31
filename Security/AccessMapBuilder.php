<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Routing\RouteCollection;
use Yarhon\RouteGuardBundle\Controller\ControllerNameResolverInterface;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadataFactory;
use Yarhon\RouteGuardBundle\Routing\RouteMetadataFactory;
use Yarhon\RouteGuardBundle\Exception\ExceptionInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapBuilder implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var RouteTestCollector
     */
    private $routeTestCollector;

    /**
     * @var ControllerNameResolverInterface
     */
    private $controllerNameResolver;

    /**
     * @var ControllerMetadataFactory
     */
    private $controllerMetadataFactory;

    /**
     * @var RouteMetadataFactory
     */
    private $routeMetadataFactory;

    /**
     * @var array
     */
    private $options;

    /**
     * AccessMapBuilder constructor.
     *
     * @param RouteTestCollector              $routeTestCollector
     * @param ControllerNameResolverInterface $controllerNameResolver
     * @param ControllerMetadataFactory       $controllerMetadataFactory
     * @param RouteMetadataFactory            $routeMetadataFactory
     * @param array                           $options
     */
    public function __construct(RouteTestCollector $routeTestCollector, ControllerNameResolverInterface $controllerNameResolver, ControllerMetadataFactory $controllerMetadataFactory, RouteMetadataFactory $routeMetadataFactory, $options = [])
    {
        $this->routeTestCollector = $routeTestCollector;
        $this->controllerNameResolver = $controllerNameResolver;
        $this->controllerMetadataFactory = $controllerMetadataFactory;
        $this->routeMetadataFactory = $routeMetadataFactory;

        $this->options = array_merge([
            'ignore_controllers' => [],
            'catch_exceptions' => false,
        ], $options);
    }

    /**
     * @param RouteCollection $routeCollection
     *
     * @return \Generator
     */
    public function build(RouteCollection $routeCollection)
    {
        if ($this->logger) {
            $this->logger->info('Build access map. Route collection count', ['count' => count($routeCollection)]);
        }

        $ignoredRoutes = [];
        $catchExceptions = $this->options['catch_exceptions'] && $this->logger;

        foreach ($routeCollection as $routeName => $route) {
            try {
                $controller = $route->getDefault('_controller');
                $controllerName = $this->controllerNameResolver->resolve($controller);

                if (null !== $controllerName && $this->isControllerIgnored($controllerName)) {
                    $ignoredRoutes[] = $routeName;
                    continue;
                }

                $controllerMetadata = $controllerName ? $this->controllerMetadataFactory->createMetadata($controllerName) : null;
                $routeMetadata = $this->routeMetadataFactory->createMetadata($route);

                // Note: currently empty arrays (no tests) are also added to authorization cache
                $tests = $this->routeTestCollector->getTests($routeName, $route, $controllerMetadata);

                yield $routeName => [$tests, $routeMetadata, $controllerMetadata];

            } catch (ExceptionInterface $e) {
                if (!$catchExceptions) {
                    throw $e;
                }

                $this->logger->error(sprintf('Exception caught while processing route "%s": %s', $routeName, $e->getMessage()), ['exception' => $e]);
                continue;
            }
        }

        // TODO: add exception routes to ignored log message?

        if ($this->logger && count($ignoredRoutes)) {
            $this->logger->info('Ignored routes', ['count' => count($ignoredRoutes), 'list' => $ignoredRoutes]);
        }
    }

    /**
     * @param string $controllerName
     *
     * @return bool
     */
    private function isControllerIgnored($controllerName)
    {
        foreach ($this->options['ignore_controllers'] as $ignored) {
            if (0 === strpos($controllerName, $ignored)) {
                return true;
            }
        }

        return false;
    }
}
