<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Routing;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Yarhon\RouteGuardBundle\Routing\UrlDeferred;
use Yarhon\RouteGuardBundle\Exception\LogicException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class UrlDeferredTest extends TestCase
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    private $requestContext;

    public function setUp()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $this->requestContext = $this->createMock(RequestContext::class);

        $this->urlGenerator->method('getContext')
            ->willReturn($this->requestContext);
    }

    /**
     * @dataProvider generateDataProvider
     */
    public function testGenerate($arguments, $expectedReferenceType, $expectedUrl)
    {
        $urlDeferred = new UrlDeferred(...$arguments);

        $urlGeneratorArguments = array_slice($arguments, 0, 2);
        $urlGeneratorArguments += [1 => [], 2 => $expectedReferenceType];

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(...$urlGeneratorArguments)
            ->willReturn('/url');

        $urlDeferred->generate($this->urlGenerator);

        $this->assertSame($expectedUrl, $urlDeferred->getGeneratedUrl());
    }

    public function generateDataProvider()
    {
        return [
            [
                ['route1', [], UrlGeneratorInterface::ABSOLUTE_URL],
                UrlGeneratorInterface::ABSOLUTE_URL,
                '/url',
            ],
            [
                ['route1', [], UrlGeneratorInterface::ABSOLUTE_PATH],
                UrlGeneratorInterface::ABSOLUTE_PATH,
                '/url',
            ],
            [
                ['route1', [], UrlGeneratorInterface::NETWORK_PATH],
                UrlGeneratorInterface::NETWORK_PATH,
                '/url',
            ],
            [
                ['route1', [], UrlGeneratorInterface::RELATIVE_PATH],
                UrlGeneratorInterface::ABSOLUTE_URL,
                null,
            ],
        ];
    }

    public function testGenerateMultipleCalls()
    {
        $urlDeferred = new UrlDeferred('route1');

        $this->urlGenerator->expects($this->once())
            ->method('generate');

        $urlDeferred->generate($this->urlGenerator);
        $urlDeferred->generate($this->urlGenerator);
    }

    /**
     * @dataProvider getPathInfoDataProvider
     */
    public function testGetPathInfo($url, $contextBaseUrl, $expected)
    {
        $urlDeferred = new UrlDeferred('route1');

        $this->urlGenerator->method('generate')
            ->willReturn($url);

        $this->requestContext->method('getBaseUrl')
            ->willReturn($contextBaseUrl);

        $urlDeferred->generate($this->urlGenerator);

        $this->assertSame($expected, $urlDeferred->getPathInfo());
    }

    public function getPathInfoDataProvider()
    {
        return [
            [
                'http://example.com/dir/file',
                '',
                '/dir/file',
            ],
            [
                'http://example.com/dir/file',
                '/dir',
                '/file',
            ],
            [
                'http://example.com/dir/file',
                '/dir/file',
                '/',
            ],
            [
                '/dir/file',
                '',
                '/dir/file',
            ],
            [
                '//example.com/dir/file',
                '',
                '/dir/file',
            ],
        ];
    }

    /**
     * @dataProvider getHostDataProvider
     */
    public function testGetHost($url, $contextHost, $expected)
    {
        $urlDeferred = new UrlDeferred('route1');

        $this->urlGenerator->method('generate')
            ->willReturn($url);

        $this->requestContext->method('getHost')
            ->willReturn($contextHost);

        $urlDeferred->generate($this->urlGenerator);

        $this->assertSame($expected, $urlDeferred->getHost());
    }

    public function getHostDataProvider()
    {
        return [
            [
                'http://example.com/dir/file',
                '',
                'example.com',
            ],
            [
                '//example.com/dir/file',
                '',
                'example.com',
            ],
            [
                '/dir/file',
                'example.com',
                'example.com',
            ],
        ];
    }

    public function testGetHostException()
    {
        $urlDeferred = new UrlDeferred('route1');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('You have to call generate() method on UrlDeferred instance prior to calling getHost().');

        $urlDeferred->getHost();
    }

    public function testGetPathInfoException()
    {
        $urlDeferred = new UrlDeferred('route1');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('You have to call generate() method on UrlDeferred instance prior to calling getPathInfo().');

        $urlDeferred->getPathInfo();
    }
}
