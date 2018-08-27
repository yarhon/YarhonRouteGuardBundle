<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\TestResolver;

use Yarhon\RouteGuardBundle\Security\Test\AbstractTestBagInterface;
use Yarhon\RouteGuardBundle\Security\Test\TestBagInterface;
use Yarhon\RouteGuardBundle\Exception\LogicException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioSecurityResolver implements TestResolverInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName() {

        return 'sensio_security';
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(AbstractTestBagInterface $testBag)
    {
        if (!($testBag instanceof TestBagInterface)) {
            throw new LogicException(sprintf('%s expects instance of %s.', __CLASS__, TestBagInterface::class));
        }

        // See \Sensio\Bundle\FrameworkExtraBundle\EventListener\IsGrantedListener::onKernelControllerArguments
        // See \Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener::onKernelControllerArguments
    }
}
