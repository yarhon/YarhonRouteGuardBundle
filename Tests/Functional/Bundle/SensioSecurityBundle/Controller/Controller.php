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
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class Controller extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction()
    {
        return $this->render('@SensioSecurity/index.html.twig');
    }

    /**
     * @Route("/public_action", name="public_action")
     */
    public function publicAction()
    {
        return new Response('user action');
    }

    /**
     * @Route("/user_action", name="user_action")
     * @IsGranted("ROLE_USER", subject="argument")
     */
    public function securedByIsGrantedAction($argument = 10)
    {
        return new Response('user action');
    }

    /**
     * @Route("/admin_action", name="admin_action")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function securedBySecurityAction($argument)
    {
        return new Response('admin action');
    }
}