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
 * TestArguments is a value object class for storing arguments for AuthorizationChecker::isGranted() authorization test.
 *
 * @see \Symfony\Component\Security\Core\Authorization\AuthorizationChecker::isGranted
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class TestArguments
{
    /**
     * @var mixed[]
     */
    private $attributes = [];

    /**
     * @var array
     */
    private $subjectMetadata;

    /**
     * @var mixed
     */
    private $subject;

    /**
     * @param mixed[] $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return mixed[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $name
     * @param mixed  $metadata
     */
    public function setSubjectMetadata($name, $metadata = null)
    {
        $this->subjectMetadata = [$name, $metadata];
    }

    /**
     * @return bool
     */
    public function requiresSubject()
    {
        return null !== $this->subjectMetadata;
    }

    /**
     * @return array
     */
    public function getSubjectMetadata()
    {
        return $this->subjectMetadata;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }
}
