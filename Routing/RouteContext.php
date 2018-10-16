<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Routing;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteContext implements RouteContextInterface, GeneratedUrlAwareInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var string
     */
    private $method;

    /**
     * @var int
     */
    private $referenceType;

    /**
     * @var string
     */
    private $generatedUrl;

    /**
     * @param string $name
     * @param array  $parameters
     * @param string $method
     */
    public function __construct($name, array $parameters = [], $method = 'GET')
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->method = $method;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * {@inheritdoc}
     */
    public function setReferenceType($referenceType)
    {
        $this->referenceType = $referenceType;
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceType()
    {
        return $this->referenceType;
    }

    /**
     * {@inheritdoc}
     */
    public function setGeneratedUrl($generatedUrl)
    {
        $this->generatedUrl = $generatedUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getGeneratedUrl()
    {
        return $this->generatedUrl;
    }
}
