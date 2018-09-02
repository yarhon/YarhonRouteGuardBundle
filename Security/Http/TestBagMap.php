<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Http;

use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBag;
use Yarhon\RouteGuardBundle\Security\Test\TestBagInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestBagMap extends AbstractTestBag implements TestBagMapInterface
{
    /**
     * TestBagMap constructor.
     *
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
    public function add(TestBagInterface $testBag, RequestConstraintInterface $constraint = null)
    {
        $this->elements[] = [$testBag, $constraint];
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(RequestContext $requestContext)
    {
        $resolved = null;

        foreach ($this->elements as $item) {
            /** @var RequestConstraintInterface $requestConstraint */
            list($testBag, $requestConstraint) = $item;

            if (null === $requestConstraint || $requestConstraint->matches($requestContext)) {
                $resolved = $testBag;
                break;
            }
        }

        return $resolved;
    }
}
