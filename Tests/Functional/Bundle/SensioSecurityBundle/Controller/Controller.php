<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Functional\Bundle\SensioSecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class Controller extends AbstractController
{
    /**
     * @Route("/action1", name="action1")
     */
    public function action1()
    {
        return $this->render('@SensioSecurity/action1.html.twig');
    }
}