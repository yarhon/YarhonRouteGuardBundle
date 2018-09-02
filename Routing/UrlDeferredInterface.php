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
use Yarhon\RouteGuardBundle\Exception\LogicException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface UrlDeferredInterface
{
    /**
     * @param UrlGeneratorInterface $urlGenerator
     *
     * @return self
     */
    public function generate(UrlGeneratorInterface $urlGenerator);

    /**
     * @return string
     *
     * @throws LogicException
     */
    public function getHost();

    /**
     * @return string
     *
     * @throws LogicException
     */
    public function getPathInfo();

    /**
     * @return string
     */
    public function getGeneratedUrl();
}
