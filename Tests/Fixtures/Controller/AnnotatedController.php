<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Fixtures\Controller;

use Yarhon\LinkGuardBundle\Tests\Fixtures\Annotation\TestOne;
use Yarhon\LinkGuardBundle\Tests\Fixtures\Annotation\TestTwo;
use Yarhon\LinkGuardBundle\Tests\Fixtures\Annotation\TestThree;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * @TestOne("v1")
 */
class AnnotatedController
{
    /**
     * @TestThree("v4")
     */
    public function show1()
    {

    }

    /**
     * @TestTwo("v2")
     * @TestOne("v3")
     */
    public function show2()
    {

    }
}