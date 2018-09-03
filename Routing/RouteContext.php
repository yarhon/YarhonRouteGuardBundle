<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Routing;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteContext implements RouteContextInterface
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
     * @var UrlDeferredInterface
     */
    private $urlDeferred;

    /**
     * @param string $name
     * @param array  $parameters
     * @param string $method
     * @param int    $referenceType
     */
    public function __construct($name, array $parameters = [], $method = 'GET', $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->method = $method;
        $this->referenceType = $referenceType;
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
    public function getReferenceType()
    {
        return $this->referenceType;
    }

    /**
     * {@inheritdoc}
     */
    public function createUrlDeferred()
    {
        if (!$this->urlDeferred) {
            $this->urlDeferred = new UrlDeferred($this->name, $this->parameters, $this->referenceType);
        }

        return $this->urlDeferred;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrlDeferred()
    {
        return $this->urlDeferred;
    }
}
