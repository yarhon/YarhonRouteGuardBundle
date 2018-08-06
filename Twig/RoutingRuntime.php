<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Twig;

use Twig\Extension\RuntimeExtensionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RoutingRuntime implements RuntimeExtensionInterface
{
    // TODO: check \Symfony\Bridge\Twig\Extension\RoutingExtension::isUrlGenerationSafe

    // url:     $relative ? UrlGeneratorInterface::NETWORK_PATH  : UrlGeneratorInterface::ABSOLUTE_URL
    //                      '//example.com/dir/file'               'http://example.com/dir/file'
    // difference in scheme

    // path:    $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH
    //                      '../parent-file'                       '/dir/file'

    /**
     * @param string $name
     * @param array  $parameters
     * @param string $method
     * @param int    $referenceType One UrlGeneratorInterface constants
     *
     * @return string|bool
     */
    protected function generate($name, $parameters = [], $method = 'GET', $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        // TODO: implement this
        return true;
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param string $method
     * @param array  $generateAs
     *
     * @return string|bool
     */
    public function link($name, $parameters = [], $method = 'GET', array $generateAs = [])
    {
        // TODO: transform $generateAs

        return $this->generate($name, $parameters, $method, $referenceType);
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param string $method
     * @param bool   $relative
     *
     * @return string|bool
     */
    public function path($name, $parameters = [], $method = 'GET', $relative = false)
    {
        $referenceType = $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH;

        return $this->generate($name, $parameters, $method, $referenceType);
    }

    /**
     * @param string $name
     * @param array  $parameters
     * @param string $method
     * @param bool   $relative
     *
     * @return string|bool
     */
    public function url($name, $parameters = [], $method = 'GET', $relative = false)
    {
        $referenceType = $relative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL;

        return $this->generate($name, $parameters, $method, $referenceType);
    }
}
