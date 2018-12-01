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
 * AbstractSymfonySecurityTest is a value object class for storing arguments for AuthorizationChecker::isGranted() authorization test.
 *
 * Note: we are using \Serializable interface here, because symfony/var-exporter does not
 * properly handles objects that have properties inherited from parent abstract class.
 * And since Symfony 4.2 symfony/cache uses symfony/var-exporter to store / retrieve values.
 *
 * @see \Symfony\Component\Security\Core\Authorization\AuthorizationChecker::isGranted
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
abstract class AbstractSymfonySecurityTest implements TestInterface, \Serializable
{
    /**
     * @var array
     */
    protected $attributes;

    /**
     * @var mixed
     */
    protected $subject;

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

    public function serialize()
    {
        return serialize([$this->attributes, $this->subject]);
    }

    public function unserialize($data)
    {
        list($attributes, $subject) = unserialize($data);
        $this->attributes = $attributes;
        $this->subject = $subject;
    }
}
