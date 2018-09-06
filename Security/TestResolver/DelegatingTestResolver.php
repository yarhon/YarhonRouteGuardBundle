<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestResolver;

use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class DelegatingTestResolver implements TestResolverInterface
{
    /**
     * @var TestResolverInterface[]
     */
    private $resolvers;

    public function __construct(array $resolvers)
    {
        foreach ($resolvers as $resolver) {
            $this->addResolver($resolver);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(AbstractTestBagInterface $testBag, RouteContextInterface $routeContext)
    {
        if (!isset($this->testResolvers[$testBag->getProviderName()])) {
            throw new RuntimeException(sprintf('No resolver exists for provider "%"', $testBag->getProviderName()));
        }

        $resolver = $this->resolvers[$testBag->getProviderName()];

        return $resolver->resolve($testBag, $routeContext);
    }

    /**
     * @param TestResolverInterface $resolver
     */
    private function addResolver(TestResolverInterface $resolver)
    {
        $this->resolvers[$resolver->getName()] = $resolver;
    }
}
