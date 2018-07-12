<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Yarhon\LinkGuardBundle\DependencyInjection\YarhonLinkGuardExtension;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class YarhonLinkGuardExtensionTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $builder;

    public function setUp()
    {
        $extension = new YarhonLinkGuardExtension();
        $this->builder = new ContainerBuilder(new ParameterBag([]));
        $this->builder->registerExtension($extension);
        $this->builder->register('security.authorization_checker')->setSynthetic(true);

        $config = [
            'cache_dir' => 'link-guard',
            'override_url_generator' => true,
        ];

        $this->builder->loadFromExtension($extension->getAlias(), $config);
    }

    public function testConfigParametersAreSet()
    {
        $this->markTestIncomplete('Watch for config changes.');

        $this->builder->getCompilerPassConfig()->setOptimizationPasses([]);
        $this->builder->getCompilerPassConfig()->setRemovingPasses([]);
        $this->builder->compile();

        // ..................


    }

    public function testPrivateServices()
    {
        $services = [
            'Yarhon\LinkGuardBundle\Security\AccessMapBuilder',
            'Yarhon\LinkGuardBundle\Security\Authorization\AuthorizationManager',
        ];

        $aliases = [
            'link_guard.authorization_manager',
            'Yarhon\LinkGuardBundle\Security\Authorization\AuthorizationManagerInterface',
        ];

        $this->builder->getCompilerPassConfig()->setOptimizationPasses([]);
        $this->builder->getCompilerPassConfig()->setRemovingPasses([]);
        $this->builder->compile();

        foreach ($services as $id) {
            $this->assertTrue($this->builder->hasDefinition($id), $id);
        }

        foreach ($aliases as $id) {
            $this->assertTrue($this->builder->hasAlias($id), $id);
        }

        $this->markTestIncomplete('Watch for service changes.');
    }

    public function testPublicServices()
    {
        $services = [
            'link_guard.authorization_manager',
        ];

        $this->builder->compile();

        foreach ($services as $id) {
            $this->assertTrue($this->builder->hasDefinition($id), $id);
        }
    }

    public function testParameters()
    {
        $parameters = [
            'link_guard.router_service_id' => 'router.default',
        ];

        $this->builder->compile();

        foreach ($parameters as $key => $value) {
            $this->assertTrue($this->builder->hasParameter($key), $key);
            $this->assertEquals($value, $this->builder->getParameter($key), $key);
        }
    }

    private function getDefinitions()
    {
        $defined = array_keys($this->builder->getDefinitions());
        $defined = array_diff($defined, ['service_container', 'kernel', 'security.authorization_checker']);
        sort($defined);

        return $defined;
    }

    private function getAliases()
    {
        $defined = array_keys($this->builder->getAliases());
        $defined = array_diff($defined, ['Psr\Container\ContainerInterface', 'Symfony\Component\DependencyInjection\ContainerInterface']);
        sort($defined);

        return $defined;
    }

    private function getParameters()
    {
        $defined = $this->builder->getParameterBag()->all();
        ksort($defined);

        return $defined;
    }
}
