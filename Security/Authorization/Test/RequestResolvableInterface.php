<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Security\Authorization\Test;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
interface RequestResolvableInterface
{
    public function resolve(Request $request);
}
