<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\DependencyInjection\Configurator;

use Symfony\Component\Routing\RouterInterface;
use Yarhon\LinkGuardBundle\Security\AccessMapBuilder;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapBuilderConfigurator
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * AccessMapBuilderConfigurator constructor.
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param AccessMapBuilder $accessMapBuilder
     */
    public function configure(AccessMapBuilder $accessMapBuilder)
    {
        $accessMapBuilder->setRouteCollection($this->router->getRouteCollection());
    }
}
