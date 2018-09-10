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
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var int
     */
    private $referenceType;

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
     * @param string $name
     * @param array  $parameters
     * @param int    $referenceType
     */
    public function __construct($name, array $parameters = [], $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->referenceType = $referenceType;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(UrlGeneratorInterface $urlGenerator)
    {
        if ($this->generated) {
            return $this;
        }

        $referenceType = $this->referenceType;

        // We need to parse path and host from the generated url, that depends on reference type.
        // When using ABSOLUTE_URL or NETWORK_PATH, generated url will contain both path and host.
        // When using ABSOLUTE_PATH, generated url will contain only host.
        // If route has some specific host assigned, the UrlGenerator will force reference type to ABSOLUTE_URL or NETWORK_PATH,
        // that would produce url with host.
        // So, with ABSOLUTE_PATH and RELATIVE_PATH, if generated url does not contains host, we can be sure
        // that the host is the "current" host, and grab it from UrlGenerator context.
        // Finally, with RELATIVE_PATH we can't simply determine path, so we use ABSOLUTE_URL,
        // and don't save the generated url.

        if (UrlGeneratorInterface::RELATIVE_PATH === $referenceType) {
            $referenceType = UrlGeneratorInterface::ABSOLUTE_URL;
        }

        $url = $urlGenerator->generate($this->name, $this->parameters, $referenceType);

        $this->parseUrl($url, $urlGenerator->getContext());

        // TODO: produce generated url with original $referenceType for RELATIVE_PATH
        if ($referenceType === $this->referenceType) {
            $this->generatedUrl = $url;
        }

        $this->generated = true;

        return $this;
    }

    private function parseUrl($url, RequestContext $urlContext)
    {
        $this->host = parse_url($url, PHP_URL_HOST) ?: $urlContext->getHost();

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
