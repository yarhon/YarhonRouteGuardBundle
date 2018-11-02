<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestProvider;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Routing\Route;
use Yarhon\RouteGuardBundle\Controller\ControllerMetadata;
use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Exception\LogicException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestProviderAggregate implements LoggerAwareInterface
{
    /**
     * @var TestProviderInterface[]
     */
    private $testProviders;

    /**
     * @var LoggerInterface;
     */
    private $logger;

    /**
     * @param \Traversable|TestProviderInterface[] $testProviders
     */
    public function __construct($testProviders = [])
    {
        $this->testProviders = $testProviders;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        foreach ($this->testProviders as $provider) {
            $provider->setLogger($this->logger);
        }
    }

    /**
     * @param string                  $routeName
     * @param Route                   $route
     * @param ControllerMetadata|null $controllerMetadata
     *
     * @return AbstractTestBagInterface[]
     *
     * @throws LogicException
     */
    public function getTests($routeName, Route $route, ControllerMetadata $controllerMetadata = null)
    {
        if (0 === count($this->testProviders)) {
            throw new LogicException('Test providers collection is empty.');
        }

        $testBags = [];

        foreach ($this->testProviders as $provider) {
            $testBag = $provider->getTests($routeName, $route, $controllerMetadata);

            if (null !== $testBag) {
                $testBag->setProviderClass(get_class($provider));
                $testBags[] = $testBag;
            }
        }

        return $testBags;
    }
}
