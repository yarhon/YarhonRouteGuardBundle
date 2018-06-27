<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NeonLight\SecureLinksBundle\Security\Provider;

use Symfony\Component\Routing\Route;
use NeonLight\SecureLinksBundle\Annotations\Parser;
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
     * @var Parser
     */
    private $parser;

    /**
     * SensioSecurityProvider constructor.
     *
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser = $parser;

        $this->parser->addAnnotationToParse('security', Security::class);
        $this->parser->addAnnotationToParse('isGranted', IsGranted::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteRules(Route $route)
    {
        return [];
    }
}
