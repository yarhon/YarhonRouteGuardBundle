<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Functional\app;

use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class Kernel extends BaseKernel
{
    const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    private $testVarDir;
    private $testBundles;
    private $testConfigs;

    public function __construct($varDir, $bundles, $configs, $environment, $debug)
    {
        $this->testVarDir = $varDir;
        $this->testBundles = $bundles;
        $this->testConfigs = $configs;

        parent::__construct($environment, $debug);
    }

    public function registerBundles()
    {
        $bundles = [];

        foreach ($this->testBundles as $class) {
            $bundles[] = new $class();
        }

        return $bundles;
    }

    public function getCacheDir()
    {
        return $this->testVarDir.'/cache/'.$this->environment;
    }

    public function getLogDir()
    {
        return $this->testVarDir.'/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $confDir = __DIR__.'/config';

        $loader->load($confDir.'/*'.self::CONFIG_EXTS, 'glob');

        $loader->load(function (ContainerBuilder $container) {
            foreach ($this->testConfigs as $extension => $config) {
                $container->loadFromExtension($extension, $config);
            }
        });
    }

    /*
    protected function build(ContainerBuilder $container)
    {
        $container->register('logger', \Psr\Log\NullLogger::class);
    }
    */
}
