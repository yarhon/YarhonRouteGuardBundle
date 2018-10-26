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
use Yarhon\RouteGuardBundle\Controller\ControllerMetadataFactory;
use Yarhon\RouteGuardBundle\Routing\RouteMetadataFactory;
use Yarhon\RouteGuardBundle\Exception\CatchableExceptionInterface;

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
     * @param RouteTestCollector        $routeTestCollector
     * @param ControllerMetadataFactory $controllerMetadataFactory
     * @param RouteMetadataFactory      $routeMetadataFactory
     * @param array                     $options
     */
    public function __construct(RouteTestCollector $routeTestCollector, ControllerMetadataFactory $controllerMetadataFactory, RouteMetadataFactory $routeMetadataFactory, $options = [])
    {
        $this->routeTestCollector = $routeTestCollector;
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

        foreach ($routeCollection as $routeName => $route) {
            $routeInfo = null;

            try {
                $controllerMetadata = $this->controllerMetadataFactory->createMetadata($route);
                $routeMetadata = $this->routeMetadataFactory->createMetadata($route);

                if (null !== $controllerMetadata && $this->isControllerIgnored($controllerMetadata->getName())) {
                    $ignoredRoutes[] = $routeName;
                } else {
                    // Note: empty arrays are also added to authorization cache
                    $tests = $this->routeTestCollector->getTests($routeName, $route, $controllerMetadata);
                    $routeInfo = [$tests, $routeMetadata, $controllerMetadata];
                }
            } catch (CatchableExceptionInterface $e) {
                if (!$this->options['catch_exceptions']) {
                    throw $e;
                }

                if ($this->logger) {
                    $this->logger->error(sprintf('Exception caught while processing route "%s": %s', $routeName, $e->getMessage()), ['exception' => $e]);
                }
            }

            yield $routeName => $routeInfo;
        }

        if ($this->logger && count($ignoredRoutes)) {
            $this->logger->info('Ignored routes count', ['count' => count($ignoredRoutes)]);
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
