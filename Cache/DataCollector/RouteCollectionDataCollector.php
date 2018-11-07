<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Cache\DataCollector;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Routing\RouteCollection;
use Yarhon\RouteGuardBundle\Controller\ControllerNameResolverInterface;
use Yarhon\RouteGuardBundle\Exception\ExceptionInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteCollectionDataCollector implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var RouteDataCollector
     */
    private $routeDataCollector;

    /**
     * @var ControllerNameResolverInterface
     */
    private $controllerNameResolver;

    /**
     * @var array
     */
    private $options;

    /**
     * @param RouteDataCollector              $routeDataCollector
     * @param ControllerNameResolverInterface $controllerNameResolver
     * @param array                           $options
     */
    public function __construct(RouteDataCollector $routeDataCollector, ControllerNameResolverInterface $controllerNameResolver, $options = [])
    {
        $this->routeDataCollector = $routeDataCollector;
        $this->controllerNameResolver = $controllerNameResolver;

        $this->options = array_merge([
            'ignore_controllers' => [],
            'ignore_exceptions' => false,
        ], $options);
    }

    /**
     * @param RouteCollection $routeCollection
     *
     * @return array
     *
     * @throws ExceptionInterface
     */
    public function collect(RouteCollection $routeCollection)
    {
        if ($this->logger) {
            $this->logger->info('Collect data for route collection', ['count' => count($routeCollection)]);
        }

        $ignoredRoutes = [];
        $catchExceptions = $this->options['ignore_exceptions'] && $this->logger;

        $data = [];

        foreach ($routeCollection as $routeName => $route) {
            try {
                $controller = $route->getDefault('_controller');
                $controllerName = $this->controllerNameResolver->resolve($controller);

                if (null !== $controllerName && $this->isControllerIgnored($controllerName)) {
                    $ignoredRoutes[] = $routeName;
                    continue;
                }

                $data[$routeName] = $this->routeDataCollector->collect($routeName, $route, $controllerName);
            } catch (ExceptionInterface $e) {
                if (!$catchExceptions) {
                    $this->addRouteNameToException($e, $routeName);
                    throw $e;
                }

                $this->logger->error(sprintf('Route "%s" would be ignored because of exception caught: %s', $routeName, $e->getMessage()), ['exception' => $e]);
                continue;
            }
        }

        // TODO: add exception routes to ignored log message?

        if ($this->logger && count($ignoredRoutes)) {
            $this->logger->info('Ignored routes', ['count' => count($ignoredRoutes), 'list' => $ignoredRoutes]);
        }

        return $data;
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

    private function addRouteNameToException(\Exception $e, $routeName)
    {
        $message = sprintf('Route "%s": %s', $routeName, $e->getMessage());

        $r = new \ReflectionProperty($e,'message');
        $r->setAccessible(true);
        $r->setValue($e, $message);
    }
}
