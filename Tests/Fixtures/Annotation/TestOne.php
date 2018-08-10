<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Fixtures\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * @Annotation
 */
class TestOne
{
    private $value;

    public function __construct(array $arguments)
    {
        $this->value = $arguments['value'];
    }
}
