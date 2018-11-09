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
        $this->resolvers = $resolvers;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(TestInterface $test)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(TestInterface $test, RouteContextInterface $routeContext)
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($test)) {
                return $resolver->resolve($test, $routeContext);
            }
        }

        throw new RuntimeException(sprintf('No resolver exists for test instance of "%s".', get_class($test)));
    }
}
