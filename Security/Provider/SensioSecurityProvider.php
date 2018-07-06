<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Security\Provider;

use Symfony\Component\Routing\Route;
use Yarhon\LinkGuardBundle\Annotations\ControllerAnnotationReaderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * SensioSecurityProvider processes @Security & @IsGranted annotations of Sensio FrameworkExtraBundle.
 *
 * @see https://symfony.com/doc/5.0/bundles/SensioFrameworkExtraBundle/annotations/security.html
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class SensioSecurityProvider implements ProviderInterface
{
    /**
     * @var ControllerAnnotationReaderInterface
     */
    private $reader;

    /**
     * SensioSecurityProvider constructor.
     *
     * @param ControllerAnnotationReaderInterface $reader
     */
    public function __construct(ControllerAnnotationReaderInterface $reader)
    {
        $this->reader = $reader;

        $this->reader->addAnnotationToRead('security', Security::class);
        $this->reader->addAnnotationToRead('isGranted', IsGranted::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteRules(Route $route)
    {
        return [];
    }
}
