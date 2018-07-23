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
use Doctrine\Common\Annotations\AnnotationReader;
use Yarhon\LinkGuardBundle\Annotations\ClassMethodAnnotationReader;
use Yarhon\LinkGuardBundle\Tests\Fixtures\Controller\AnnotatedController;
use Yarhon\LinkGuardBundle\Tests\Fixtures\Annotation\TestOne;
use Yarhon\LinkGuardBundle\Tests\Fixtures\Annotation\TestTwo;
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

        $reader->addAnnotationClass(TestOne::class);
        $reader->addAnnotationClass(TestTwo::class);

        $annotations = $reader->read(AnnotatedController::class, 'show1');

        $expected = [
            new TestOne(['value' => 'v1']),
        ];

        $this->assertEquals($expected, $annotations);

        $annotations = $reader->read(AnnotatedController::class, 'show2');

        $expected = [
            new TestOne(['value' => 'v1']),
            new TestTwo(['value' => 'v2']),
            new TestOne(['value' => 'v3']),
        ];

        $this->assertEquals($expected, $annotations);
    }
}
