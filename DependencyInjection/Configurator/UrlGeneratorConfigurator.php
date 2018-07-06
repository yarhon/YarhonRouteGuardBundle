<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\DependencyInjection\Configurator;

use Symfony\Component\Routing\Router;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Yarhon\LinkGuardBundle\Routing\UrlGenerator;
use Yarhon\LinkGuardBundle\Routing\UrlGeneratorIAwareRouterInterface;
use Yarhon\LinkGuardBundle\Routing\AuthorizationAwareUrlGeneratorInterface;
use Yarhon\LinkGuardBundle\Security\Authorization\AuthorizationManagerInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class UrlGeneratorConfigurator
{
    /**
     * @var AuthorizationManagerInterface
     */
    private $authorizationManager;

    /**
     * @var bool
     */
    private $overrideClass;


    /**
     * UrlGeneratorConfigurator constructor.
     *
     * @param AuthorizationManagerInterface $authorizationManager
     * @param bool                          $overrideClass
     */
    public function __construct(AuthorizationManagerInterface $authorizationManager, $overrideClass = false)
    {
        $this->authorizationManager = $authorizationManager;
        $this->overrideClass = $overrideClass;
    }

    /**
     * This class is intended to configure UrlGenerator, but technically configures Router,
     * because UrlGenerator is created (instantiated) inside \Symfony\Component\Routing\Router::getGenerator.
     *
     * @param $router
     */
    public function configure($router)
    {
        /*
         * We perform the following check (and not using Router type-hint), because someone can use
         * different implementation of the Router class that is not extended from the basic one.
         * And, unfortunately, Router class doesn't implement any interface(s) that we can rely on
         * (i.e., interface(s) with "setOption" and "getGenerator" methods).
         */
        if (!($router instanceof Router)) {
            //TODO: think about more informative action
            return;
        }

        if ($this->overrideClass) {
            $router->setOption('generator_class', UrlGenerator::class);
            $router->setOption('generator_base_class', UrlGenerator::class);
        }

        $generator = $router->getGenerator();

        if ($generator instanceof AuthorizationAwareUrlGeneratorInterface) {
            $generator->setAuthorizationManager($this->authorizationManager);
        }
    }
}