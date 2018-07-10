<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Tests\Annotations;

use PHPUnit\Framework\TestCase;
use Yarhon\LinkGuardBundle\Security\Provider\SymfonyAccessControlProvider;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonyAccessControlProviderTest extends TestCase
{
    /**
     * @dataProvider addRuleProvider
     */
    public function testAddRule($rule, $expected)
    {
        $provider = new SymfonyAccessControlProvider();
        $provider->addRule($rule);

        $expected = [0 => $expected];

        // Warning: this property is private
        //$this->assertAttributeEquals($expected, 'rules', $provider);
    }

    public function addRuleProvider()
    {
        return [
            // test 1
            [
                [
                    'path' => 'test1',
                    'host' => null,
                    'methods' => [],
                    'ips' => [],
                    'roles' => [],
                    'allow_if' => null,
                ],
                [
                    

                ],
            ],
            // test 2
        ];
    }
}
