<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Routing;

use Symfony\Component\Routing\Generator\UrlGenerator as BaseUrlGenerator;
use Yarhon\LinkGuardBundle\Security\Authorization\AuthorizationManagerInterface;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class UrlGenerator extends BaseUrlGenerator implements AuthorizationAwareUrlGeneratorInterface
{
    /**
     * @var AuthorizationManagerInterface
     */
    private $authorizationManager;

    /**
     * {@inheritdoc}
     */
    public function setAuthorizationManager(AuthorizationManagerInterface $authorizationManager)
    {
        $this->authorizationManager = $authorizationManager;
    }

    /**
     * We override doGenerate() method to plug into the URL generation process.
     * !!! fix wording !!! This is done because cached url generator class (generated by \Symfony\Component\Routing\Generator\Dumper\PhpGeneratorDumper)
     * overrides parent's generate() method (without calling parent method inside).
     *
     * TODO: explore if it has sense to add security info directly cached url generator class.
     *
     * {@inheritdoc}
     */
    protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, array $requiredSchemes = array())
    {
        // checks should go here
        //$this->authorizationManager->isGranted('a');

        return parent::doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, $requiredSchemes);
    }
}
