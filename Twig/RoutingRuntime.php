<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Twig;

use Twig\Extension\RuntimeExtensionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Yarhon\RouteGuardBundle\Routing\AuthorizedUrlGeneratorInterface;
use Yarhon\RouteGuardBundle\Exception\InvalidArgumentException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RoutingRuntime implements RuntimeExtensionInterface
{
    /**
     * @var AuthorizedUrlGeneratorInterface
     */
    protected $urlGenerator;

    public function __construct(AuthorizedUrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param string $method
     * @param array  $generateAs
     *
     * @return string|bool
     */
    public function route($name, array $parameters = [], $method = 'GET', array $generateAs = [])
    {
        $generateAsDefault = ['path', false];
        $generateAs += $generateAsDefault;

        $referenceType = null;

        if ($generateAs[0] === 'path') {
            $referenceType = $generateAs[1] ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH;
        } elseif ($generateAs[0] === 'url') {
            $referenceType = $generateAs[1] ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL;
        } else {
            throw new InvalidArgumentException(sprintf('Invalid reference type: "%s"', $generateAs));
        }

        return $this->urlGenerator->generate($name, $parameters, $method, $referenceType);
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param string $method
     * @param bool   $relative
     *
     * @return string|bool
     */
    public function path($name, array $parameters = [], $method = 'GET', $relative = false)
    {
        $referenceType = $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH;

        return $this->urlGenerator->generate($name, $parameters, $method, $referenceType);
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param string $method
     * @param bool   $relative
     *
     * @return string|bool
     */
    public function url($name, array $parameters = [], $method = 'GET', $relative = false)
    {
        $referenceType = $relative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL;

        return $this->urlGenerator->generate($name, $parameters, $method, $referenceType);
    }
}
