<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\DependencyInjection\Configurator;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ConfiguratorChain
{
    /**
     * @var array
     */
    private $configurators = [];

    public function add(callable $configurator)
    {
        $this->configurators[] = $configurator;
    }

    public function configure($service)
    {
        foreach ($this->configurators as $configurator) {
            call_user_func($configurator, $service);
        }
    }
}
