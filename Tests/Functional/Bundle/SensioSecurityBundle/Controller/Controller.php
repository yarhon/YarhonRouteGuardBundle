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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class Controller extends AbstractController
{
    /**
     * @Route("/public", name="public")
     */
    public function publicAction()
    {
        return new Response('');
    }

    /**
     * @Route("/is_granted/user_role", name="is_granted_user_role")
     * @IsGranted("ROLE_USER", subject="argument")
     */
    public function isGrantedUserRoleAction($argument = 10)
    {
        return new Response('');
    }

    /**
     * @Route("/is_granted/admin_role", name="is_granted_admin_role")
     * @IsGranted("ROLE_ADMIN", subject="argument")
     */
    public function isGrantedAdminRoleAction($argument = 10)
    {
        return new Response('');
    }

    /**
     * @Route("/security/user_role", name="security_user_role")
     * @Security("is_granted('ROLE_USER')")
     */
    public function securityUserRoleAction()
    {
        return new Response('');
    }

    /**
     * @Route("/security/admin_role", name="security_admin_role")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function securityAdminRoleAction()
    {
        return new Response('');
    }

    /**
     * @Route("/security/controller_argument/{argument}", name="security_controller_argument")
     * @Security("argument == 10")
     */
    public function securityControllerArgumentAction($argument)
    {
        return new Response('');
    }
}
