<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NeonLight\SecureLinksBundle\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RoutingRuntime
{
    // TODO: check \Symfony\Bridge\Twig\Extension\RoutingExtension::isUrlGenerationSafe

    public function isRouteGranted($name, $parameters = [], $relative = false)
    {
        // url:     $relative ? UrlGeneratorInterface::NETWORK_PATH  : UrlGeneratorInterface::ABSOLUTE_URL
        //                      '//example.com/dir/file'               'http://example.com/dir/file'
        // difference in scheme

        // path:    $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH
        //                      '../parent-file'                       '/dir/file'

        /*
        url_if_granted($name, $parameters, $relative, $method)

        path_if granted()

        {% urlifgranted discover %}

        {% urlifgranted path("secure2", { page: 10 }) %}

        {% ifroutegranted 'post' %}

        {% ifroutegranted {'path', 'secure2', { page: 10 }, isRelative, 'POST'} %}

        {% ifroutegranted 'secure2', { page: 10 } as path relative %}

        {% ifroutegranted 'secure2', { page: 10 } generate path relative %}

        in place of a link - {{ generated(path, relative) }}


        */

        return true;
    }
}
