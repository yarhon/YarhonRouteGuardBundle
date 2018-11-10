<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Functional\Bundle\SymfonyAccessControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @Route("/static_path", name="static_path")
     */
    public function staticPathAction()
    {
        return new Response('');
    }

    /**
     * @Route("/dynamic_path/{page}", name="dynamic_path")
     */
    public function dynamicPathAction()
    {
        return new Response('');
    }
}
