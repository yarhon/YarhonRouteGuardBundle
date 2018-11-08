<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Yarhon\RouteGuardBundle\DependencyInjection\Compiler\SensioSecurityExpressionVoterPass;
use Yarhon\RouteGuardBundle\Security\Authorization\SensioSecurityExpressionVoter;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioSecurityExpressionVoterPassTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var SensioSecurityExpressionVoterPass
     */
    private $pass;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->container->register(SensioSecurityExpressionVoter::class);
        $this->pass = new SensioSecurityExpressionVoterPass();
    }

    public function testProcessWithoutExtraBundle()
    {
        $this->pass->process($this->container);

        $this->assertFalse($this->container->hasDefinition(SensioSecurityExpressionVoter::class));
    }

    public function testProcessWithExtraBundle()
    {
        $this->container->register('sensio_framework_extra.security.listener');
        $this->container->register('sensio_framework_extra.security.expression_language.default');

        $this->pass->process($this->container);

        $this->assertTrue($this->container->hasDefinition(SensioSecurityExpressionVoter::class));
    }
}
