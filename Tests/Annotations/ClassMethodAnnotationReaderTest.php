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
use Yarhon\RouteGuardBundle\Tests\Fixtures\Controller\AnnotatedController;
use Yarhon\RouteGuardBundle\Tests\Fixtures\Annotation\TestOne;
use Yarhon\RouteGuardBundle\Tests\Fixtures\Annotation\TestTwo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ClassMethodAnnotationReaderTest extends TestCase
{
    public function testRead()
    {
        $reader = new AnnotationReader();
        $reader = new ClassMethodAnnotationReader($reader);

        $classes = [
            TestOne::class,
            TestTwo::class,
        ];

        $annotations = $reader->read(AnnotatedController::class, 'show1', $classes);

        $expected = [
            new TestOne(['value' => 'v1']),
        ];

        $this->assertEquals($expected, $annotations);

        $annotations = $reader->read(AnnotatedController::class, 'show2', $classes);

        $expected = [
            new TestOne(['value' => 'v1']),
            new TestTwo(['value' => 'v2']),
            new TestOne(['value' => 'v3']),
        ];

        $this->assertEquals($expected, $annotations);
    }
}
