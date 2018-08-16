<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface AccessMapBuilderInterface
{
    // TODO: add phpdoc
    public function build($force = false);
}
