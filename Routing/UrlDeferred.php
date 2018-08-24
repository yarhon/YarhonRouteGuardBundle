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
use Symfony\Component\Routing\RequestContext;
use Yarhon\RouteGuardBundle\Exception\LogicException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class UrlDeferred implements UrlDeferredInterface
{

    /**
     * @var UrlPrototypeInterface
     */
    private $urlPrototype;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $pathInfo;

    /**
     * @var string
     */
    private $generatedUrl;

    /**
     * @var bool
     */
    private $generated = false;

    /**
     * {@inheritdoc}
     */
    public function __construct(UrlPrototypeInterface $urlPrototype)
    {
        $this->urlPrototype = $urlPrototype;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(UrlGeneratorInterface $urlGenerator)
    {
        if ($this->generated) {
            return $this;
        }

        $referenceType = $this->urlPrototype->getReferenceType();

        if (UrlGeneratorInterface::ABSOLUTE_PATH !== $referenceType && UrlGeneratorInterface::NETWORK_PATH !== $referenceType) {
            $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH;
        }

        $url = $urlGenerator->generate($this->urlPrototype->getName(), $this->urlPrototype->getParameters(), $referenceType);

        $this->parseUrl($url, $urlGenerator->getContext());

        // TODO: produce generated url with original $referenceType for relative reference types
        if ($referenceType === $this->urlPrototype->getReferenceType()) {
            $this->generatedUrl = $url;
        }

        $this->generated = true;

        return $this;
    }

    private function parseUrl($url, RequestContext $urlContext)
    {
        $this->host = parse_url($url, PHP_URL_HOST);

        $pathInfo = parse_url($url, PHP_URL_PATH);

        $pathInfo = substr($pathInfo, strlen($urlContext->getBaseUrl()));
        if (false === $pathInfo || '' === $pathInfo) {
            // See \Symfony\Component\HttpFoundation\Request::preparePathInfo
            $pathInfo = '/';
        }

        $this->pathInfo = $pathInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrototype()
    {
        return $this->urlPrototype;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        if (!$this->generated) {
            throw new LogicException('You have to call generate() method on UrlDeferred instance prior to calling getHost().');
        }

        return $this->host;
    }

    /**
     * {@inheritdoc}
     */
    public function getPathInfo()
    {
        if (!$this->generated) {
            throw new LogicException('You have to call generate() method on UrlDeferred instance prior to calling getPathInfo().');
        }

        return $this->pathInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function getGeneratedUrl()
    {
        return $this->generatedUrl;
    }

}
