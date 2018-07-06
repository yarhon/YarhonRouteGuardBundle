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
        $this->builder = new ContainerBuilder();
        $this->builder->registerExtension(new YarhonLinkGuardExtension());
    }

    public function atestLoadServices()
    {
        $this->builder->loadFromExtension('yarhon_link_guard', []);
        $this->builder->compile();

        $servcies = [
            'Yarhon\LinkGuardBundle\Security\Provider\SymfonyAccessControlProvider'
        ];

        foreach ($servcies as $id) {
            // $this->assertTrue($this->builder->has('Yarhon\LinkGuardBundle\Security\Provider\SymfonyAccessControlProvider'));
        }


    }
}
