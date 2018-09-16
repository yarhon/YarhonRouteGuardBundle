<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Tests\Annotations;

use PHPUnit\Framework\TestCase;
use Doctrine\Common\Annotations\AnnotationReader;
use Yarhon\RouteGuardBundle\Annotations\ClassMethodAnnotationReader;
use Yarhon\RouteGuardBundle\Tests\Fixtures\Controller\SimpleController;
use Yarhon\RouteGuardBundle\Tests\Fixtures\Annotation\TestOne;
use Yarhon\RouteGuardBundle\Tests\Fixtures\Annotation\TestTwo;
use Yarhon\RouteGuardBundle\Tests\Fixtures\Annotation\TestThree;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ClassMethodAnnotationReaderTest extends TestCase
{
    private $readerDelegate;

    private $reader;

    public function setUp()
    {
        $this->readerDelegate = $this->createMock(AnnotationReader::class);
        $this->reader = new ClassMethodAnnotationReader($this->readerDelegate);
    }

    /**
     * @dataProvider readDataProvider
     */
    public function testRead($classAnnotations, $methodAnnotations, $expected)
    {
        $classesToRead = [
            TestOne::class,
            TestTwo::class,
        ];

        $this->readerDelegate->method('getClassAnnotations')
            ->willReturn($classAnnotations);

        $this->readerDelegate->method('getMethodAnnotations')
            ->willReturn($methodAnnotations);

        $annotations = $this->reader->read(SimpleController::class, 'index', $classesToRead);

        $this->assertEquals($expected, $annotations);
    }

    public function readDataProvider()
    {
        return [
            [
                [new TestOne(['value' => 'v1'])],
                [new TestThree(['value' => 'v4'])],
                [new TestOne(['value' => 'v1'])],
            ],
            [
                [new TestOne(['value' => 'v1'])],
                [new TestTwo(['value' => 'v2']), new TestOne(['value' => 'v3'])],
                [new TestOne(['value' => 'v1']), new TestTwo(['value' => 'v2']), new TestOne(['value' => 'v3'])],
            ],
        ];
    }
}
