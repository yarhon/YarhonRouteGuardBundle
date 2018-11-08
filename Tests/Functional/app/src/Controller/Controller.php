<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Functional\app\src\Controller;

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
     * @Route("/link/{routeName}", name="link")
     */
    public function linkAction(Request $request, $routeName)
    {
        $parameters = $request->query->get('parameters', []);
        $method = $request->query->get('method','GET');

        return $this->render('link.html.twig', [
            'name' => $routeName,
            'parameters' => $parameters,
            'method' => $method,
        ]);
    }
}
