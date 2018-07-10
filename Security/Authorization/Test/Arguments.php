<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Security\Authorization\Test;

/**
 * Arguments is a "value object" class for storing arguments for AuthorizationChecker::isGranted() checks.
 *
 * @see \Symfony\Component\Security\Core\Authorization\AuthorizationChecker::isGranted
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class Arguments
{
    /**
     * Indicates that subject is the name of the "context" variable (request, etc.).
     */
    const SUBJECT_CONTEXT_VARIABLE = 1;

    /**
     * Indicates that subject is the name of controller argument.
     */
    const SUBJECT_CONTROLLER_ARGUMENT = 2;

    /**
     * List of possible variable names, to be used in setSubjectMetadata() with SUBJECT_CONTEXT_VARIABLE type.
     *
     * @var array
     */
    private $contextVariables = [
        'request',
    ];

    /**
     * @var mixed[]
     */
    private $attributes = [];

    /**
     * @var array
     */
    private $subjectMetadata;

    /**
     * @param mixed $attribute
     */
    public function addAttribute($attribute)
    {
        $this->attributes[] = $attribute;
    }

    /**
     * @param mixed[] $attributes
     */
    public function setAttributes(array $attributes)
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
     * Sets name and type of the subject argument.
     * Depending on $type, subject can be one of the "context" variables, or one of the controller arguments.
     *
     * @see \Symfony\Component\Security\Http\Firewall\AccessListener::handle  "Context" variable case (Request object as the subject)
     * @see \Sensio\Bundle\FrameworkExtraBundle\EventListener\IsGrantedListener::onKernelControllerArguments    Controller argument case (Sensio FrameworkExtraBundle @IsGranted annotation)
     *
     * @param int    $type One of self::SUBJECT_* constants
     * @param string $name Subject variable / argument name
     *
     * @throws \InvalidArgumentException
     */
    public function setSubjectMetadata($type, $name)
    {
        if (!in_array($type, [self::SUBJECT_CONTEXT_VARIABLE, self::SUBJECT_CONTROLLER_ARGUMENT], true)) {
            throw new \InvalidArgumentException(sprintf('Invalid subject type: %s', $type));
        }

        if (self::SUBJECT_CONTEXT_VARIABLE === $type && !in_array($name, $this->contextVariables, true)) {
            throw new \InvalidArgumentException(sprintf('Unknown subject context variable name: %s', $name));
        }

        $this->subjectMetadata = [$type, $name];
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
}
