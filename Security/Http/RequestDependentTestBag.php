<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Http;

use Yarhon\RouteGuardBundle\Security\Test\ProviderAwareTrait;
use Yarhon\RouteGuardBundle\Security\Test\ProviderAwareInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RequestDependentTestBag implements RequestDependentTestBagInterface, ProviderAwareInterface
{
    use ProviderAwareTrait;

    /**
     * @var array
     */
    private $map = [];

    /**
     * @param array $map
     */
    public function __construct(array $map)
    {
        foreach ($map as $item) {
            $this->add(...$item);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTests(RequestContext $requestContext)
    {
        foreach ($this->map as list($tests, $requestConstraint)) {
            /** @var RequestConstraintInterface $requestConstraint */
            if (null === $requestConstraint || $requestConstraint->matches($requestContext)) {
                return $tests;
            }
        }

        return [];
    }

    /**
     * @param TestInterface[]                 $tests
     * @param RequestConstraintInterface|null $constraint
     */
    private function add(array $tests, RequestConstraintInterface $constraint = null)
    {
        $this->map[] = [$tests, $constraint];
    }
}
