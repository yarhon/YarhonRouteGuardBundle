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
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class Kernel extends BaseKernel
{
    const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    private $testVarDir;
    private $testBundles;
    private $testConfigs;
    private $routeResources;

    public function __construct($varDir, $bundles, $configs, $routeResources, $environment, $debug)
    {
        $this->testVarDir = $varDir;
        $this->testBundles = $bundles;
        $this->testConfigs = $configs;
        $this->routeResources = $routeResources;

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

    public function getProjectDir()
    {
        return __DIR__;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $confDir = __DIR__.'/config';

        $loader->load($confDir.'/*'.self::CONFIG_EXTS, 'glob');

        $loader->load(function (ContainerBuilder $container) {
            foreach ($this->testConfigs as $extension => $config) {
                $container->loadFromExtension($extension, $config);
            }

            $service = static::MAJOR_VERSION < 4 ? 'kernel:loadRoutes' : 'kernel::loadRoutes';

            $container->loadFromExtension('framework', array(
                'router' => array(
                    'resource' => $service,
                    'type' => 'service',
                ),
            ));
        });
    }

    public function loadRoutes(LoaderInterface $loader)
    {
        $routes = new RouteCollectionBuilder($loader);

        $confDir = __DIR__.'/config';
        $routes->import($confDir.'/routes/routes.yaml');

        foreach ($this->routeResources as $routeResource) {
            $routes->import(...$routeResource);
        }

        return $routes->build();
    }

    /*
    protected function build(ContainerBuilder $container)
    {
        $container->register('logger', \Psr\Log\NullLogger::class);
    }
    */
}
