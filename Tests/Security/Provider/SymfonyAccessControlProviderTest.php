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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Yarhon\LinkGuardBundle\Security\Provider\SymfonyAccessControlProvider;
use Yarhon\LinkGuardBundle\Security\Authorization\Test\Arguments;
use Yarhon\LinkGuardBundle\Security\Authorization\Test\TestBag;
use Yarhon\LinkGuardBundle\Security\Authorization\Test\TestBagMap;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SymfonyAccessControlProviderTest extends TestCase
{
    /**
     * @dataProvider addRuleProvider
     */
    public function atestAddRule($rule, $expected)
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

    public function testConcept()
    {
        /*
        $arguments = new Arguments();
        $testBag = new TestBag([$arguments]);
        $requestMatcher = new RequestMatcher();
        $request = new Request();
        $testBagMap = new TestBagMap([[$testBag, $requestMatcher]]);
        */

        $rules = [
            ['path' => "^/admin", 'roles' => "ROLE_ADMIN" ],
            ['path' => "^/secure1", 'roles' => "ROLE_SECURE_1"],
            ['path' => "^/_internal/secure", 'allow_if' => "'127.0.0.1' == request.getClientIp() or has_role('ROLE_ADMIN')"],
        ];

        $provider = new SymfonyAccessControlProvider();

        foreach ($rules as $rule) {
            $provider->addRule($rule);
        }
    }

}

