<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestResolver;

use Yarhon\RouteGuardBundle\Security\Test\TestInterface;
use Yarhon\RouteGuardBundle\Routing\RouteContextInterface;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class DelegatingTestResolver implements TestResolverInterface
{
    /**
     * @var \Traversable|TestResolverInterface[]
     */
    private $resolvers;

    /**
     * @param \Traversable|TestResolverInterface[] $resolvers
     */
    public function __construct($resolvers = [])
    {
        foreach ($resolvers as $resolver) {
            $this->addResolver($resolver);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderClass()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(TestInterface $test, RouteContextInterface $routeContext)
    {
        if (!isset($this->resolvers[$test->getProviderClass()])) {
            throw new RuntimeException(sprintf('No resolver exists for provider "%s".', $test->getProviderClass()));
        }

        $resolver = $this->resolvers[$test->getProviderClass()];

        return $resolver->resolve($test, $routeContext);
    }

    /**
     * @param TestResolverInterface $resolver
     */
    private function addResolver(TestResolverInterface $resolver)
    {
        $this->resolvers[$resolver->getProviderClass()] = $resolver;
    }
}
