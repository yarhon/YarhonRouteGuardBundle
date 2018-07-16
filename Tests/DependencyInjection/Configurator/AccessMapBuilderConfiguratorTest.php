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
use Symfony\Component\Routing\RouterInterface;
use Yarhon\LinkGuardBundle\Tests\HelperTrait;
use Yarhon\LinkGuardBundle\DependencyInjection\Configurator\AccessMapBuilderConfigurator;
use Yarhon\LinkGuardBundle\Security\AccessMapBuilder;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class AccessMapBuilderConfiguratorTest extends TestCase
{
    use HelperTrait;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var AccessMapBuilderConfigurator
     */
    private $configurator;

    public function setUp()
    {
        $this->router = $this->createMock('Symfony\Component\Routing\Router');
        $this->configurator = new AccessMapBuilderConfigurator($this->router);
    }

    public function testConfigure()
    {
        $routeCollection = $this->createRouteCollection([
            '/path1' => 'class::method',
        ]);

        $this->router->method('getRouteCollection')
            ->willReturn($routeCollection);

        $accessMapBuilder = new AccessMapBuilder();
        $this->configurator->configure($accessMapBuilder);

        // Warning: this attribute is private
        $this->assertAttributeEquals($routeCollection, 'routeCollection', $accessMapBuilder);
    }
}
