<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Test;

/**
 * SymfonySecurityTest is a value object class for storing arguments for AuthorizationChecker::isGranted() authorization test.
 *
 * @see \Symfony\Component\Security\Core\Authorization\AuthorizationChecker::isGranted
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
abstract class SymfonySecurityTest implements TestInterface
{
    /**
     * @var array
     */
    private $attributes;

    /**
     * @var mixed
     */
    private $subject;

    /**
     * @param array $attributes
     * @param mixed $subject
     */
    public function __construct(array $attributes, $subject = null)
    {
        $this->attributes = $attributes;
        $this->subject = $subject;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }
}
