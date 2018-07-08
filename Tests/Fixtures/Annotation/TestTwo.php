<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Fixtures\Annotation;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * @Annotation
 */
class TestTwo
{
    private $value;

    public function __construct(array $arguments)
    {
        $this->value = $arguments['value'];
    }
}
