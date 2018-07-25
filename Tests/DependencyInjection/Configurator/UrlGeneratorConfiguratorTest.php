<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\DependencyInjection\Configurator;

use PHPUnit\Framework\TestCase;
use Yarhon\LinkGuardBundle\Routing\UrlGenerator as OverriddenUrlGenerator;
use Yarhon\LinkGuardBundle\DependencyInjection\Configurator\UrlGeneratorConfigurator;
use Yarhon\LinkGuardBundle\Security\Authorization\AuthorizationManager;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class UrlGeneratorConfiguratorTest extends TestCase
{
    private $router;

    private $authorizationManager;

    public function setUp()
    {
        $this->router = $this->createRouterMock();

        $this->authorizationManager = $this->createMock(AuthorizationManager::class);
    }

    private function createRouterMock()
    {
        $router = $this->getMockBuilder('Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()
            ->setMethodsExcept(['getOption', 'setOption', 'setOptions', 'getGenerator', 'setContext'])
            ->getMock();

        $routeCollection = $this->createMock('Symfony\Component\Routing\RouteCollection');

        $router->method('getRouteCollection')
            ->willReturn($routeCollection);

        $context = $this->createMock('Symfony\Component\Routing\RequestContext');
        $router->setContext($context);

        // trigger set default options
        $router->setOptions([]);

        return $router;
    }

    public function testConfigureWithoutOverrideClass()
    {
        $configurator = new UrlGeneratorConfigurator($this->authorizationManager, false);

        $defaultOptions = [$this->router->getOption('generator_class'), $this->router->getOption('generator_base_class')];

        $configurator->configure($this->router);

        $options = [$this->router->getOption('generator_class'), $this->router->getOption('generator_base_class')];

        $this->assertEquals($defaultOptions, $options);
    }

    public function testConfigureWithOverrideClass()
    {
        $configurator = new UrlGeneratorConfigurator($this->authorizationManager, true);

        $configurator->configure($this->router);

        $options = [$this->router->getOption('generator_class'), $this->router->getOption('generator_base_class')];

        $this->assertEquals([OverriddenUrlGenerator::class, OverriddenUrlGenerator::class], $options);
    }

    public function testConfigureSetsAuthorizationManager()
    {
        $configurator = new UrlGeneratorConfigurator($this->authorizationManager, false);

        $this->router->setOption('generator_class', OverriddenUrlGenerator::class);
        $this->router->setOption('generator_base_class', OverriddenUrlGenerator::class);

        $configurator->configure($this->router);

        $generator = $this->router->getGenerator();

        $this->assertAttributeEquals($this->authorizationManager, 'authorizationManager', $generator);

        $this->markTestIncomplete('Watch for AuthorizationManager changes.');
    }
}
