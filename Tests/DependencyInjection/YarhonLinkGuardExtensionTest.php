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
        $this->builder = new ContainerBuilder(new ParameterBag([]));
        $this->builder->registerExtension(new YarhonLinkGuardExtension());
        $this->builder->register('security.authorization_checker')->setSynthetic(true);

        $config = [
            'cache_dir' => 'link-guard',
            'override_url_generator' => true,
        ];

        $this->builder->loadFromExtension('yarhon_link_guard', $config);
    }

    public function testConfigParametersAreSet()
    {
        $this->markTestIncomplete('Watch config changes.');

        $this->builder->getCompilerPassConfig()->setOptimizationPasses([]);
        $this->builder->getCompilerPassConfig()->setRemovingPasses([]);
        $this->builder->compile();

        // ..................
    }

    public function testPrivateServices()
    {
        $this->markTestIncomplete('Watch for service changes.');

        $services = [
            'Yarhon\LinkGuardBundle\Security\AccessMap',
            'Yarhon\LinkGuardBundle\DependencyInjection\Configurator\AccessMapConfigurator',
            'Yarhon\LinkGuardBundle\Security\Authorization\AuthorizationManager',
        ];

        $this->builder->getCompilerPassConfig()->setOptimizationPasses([]);
        $this->builder->getCompilerPassConfig()->setRemovingPasses([]);
        $this->builder->compile();

        foreach ($services as $id) {
            $this->assertTrue($this->builder->hasDefinition($id));
        }

        $aliases = [
            'link_guard.authorization_manager',
            'Yarhon\LinkGuardBundle\Security\Authorization\AuthorizationManagerInterface',
        ];

        foreach ($aliases as $id) {
            $this->assertTrue($this->builder->hasAlias($id));
        }
    }

    public function testPublicServices()
    {
        $services = [
            'link_guard.authorization_manager',
        ];

        $this->builder->compile();

        foreach ($services as $id) {
            $this->assertTrue($this->builder->hasDefinition($id));
        }
    }

    public function testParameters()
    {
        $parameters = [
            'link_guard.router_service_id' => 'router.default',
        ];

        $this->builder->compile();

        foreach ($parameters as $key => $value) {
            $this->assertTrue($this->builder->hasParameter($key));
            $this->assertEquals($value, $this->builder->getParameter($key));
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
