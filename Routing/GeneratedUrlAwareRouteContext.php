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
class GeneratedUrlAwareRouteContext extends RouteContext implements GeneratedUrlAwareInterface
{
    /**
     * @var int
     */
    private $referenceType;

    /**
     * @var string
     */
    private $generatedUrl;

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
