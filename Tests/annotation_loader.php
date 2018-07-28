<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\Common\Annotations\AnnotationRegistry;

if (!class_exists(AnnotationRegistry::class, false) && class_exists(AnnotationRegistry::class)) {
    if (method_exists(AnnotationRegistry::class, 'registerUniqueLoader')) {
        AnnotationRegistry::registerUniqueLoader('class_exists');
    } else {
        AnnotationRegistry::registerLoader('class_exists');
    }
}