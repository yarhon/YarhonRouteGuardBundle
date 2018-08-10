<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Fixtures\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * @Security("is_granted('POST_SHOW', post) and has_role('ROLE_ADMIN')")
 * @Template
 */
class SensioSecurityAnnotatedController
{
    /**
     * @IsGranted("POST_SHOW", subject="post")
     * @Template
     */
    public function show1($post)
    {
    }

    /**
     * @Security("is_granted('POST_SHOW', subject="post")
     * @IsGranted("ROLE_ADMIN")
     */
    public function show2($post)
    {
    }
}
