<?php
/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\Security\Authorization;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\ExpressionLanguage;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Yarhon\RouteGuardBundle\ExpressionLanguage\ExpressionDecorator;

/**
 * Sensio FrameworkExtraBundle executes security expressions in place, bypassing the traditional flow to
 * \Symfony\Component\Security\Core\Authorization\AuthorizationChecker and further to the Voter(s).
 * SensioSecurityExpressionVoter is intended to execute those security expressions using traditional flow.
 *
 * @see \Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener
 *
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SensioSecurityExpressionVoter extends Voter
{
    protected static $variableNames = [
        'token',
        'user',
        'object',
        'subject',
        'roles',
        'trust_resolver',
        'auth_checker',
        'request', // TODO: this variable is conditionally passed to evaluate
    ];

    /**
     * @var ExpressionLanguage
     */
    protected $expressionLanguage;

    /**
     * @var AuthenticationTrustResolverInterface
     */
    protected $trustResolver;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $authChecker;

    /**
     * @var RoleHierarchyInterface|null
     */
    protected $roleHierarchy;

    /**
     * SensioSecurityExpressionVoter constructor.
     *
     * @param ExpressionLanguage                   $expressionLanguage
     * @param AuthenticationTrustResolverInterface $trustResolver
     * @param AuthorizationCheckerInterface        $authChecker
     * @param RoleHierarchyInterface|null          $roleHierarchy
     */
    public function __construct(ExpressionLanguage $expressionLanguage, AuthenticationTrustResolverInterface $trustResolver, AuthorizationCheckerInterface $authChecker, RoleHierarchyInterface $roleHierarchy = null)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->trustResolver = $trustResolver;
        $this->authChecker = $authChecker;
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * @return array
     */
    public static function getVariableNames()
    {
        return self::$variableNames;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $attribute instanceof ExpressionDecorator;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var ExpressionDecorator $attribute */
        $expressionVariables = $attribute->getVariables();

        $variables = $this->getVariables($token, $subject);

        // In case of overlap, built-in variables win.
        $variables = array_merge($expressionVariables, $variables);

        return $this->expressionLanguage->evaluate($attribute->getExpression(), $variables);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param TokenInterface $token
     * @param mixed          $subject
     *
     * @return array
     */
    protected function getVariables(TokenInterface $token, $subject)
    {
        if (null !== $this->roleHierarchy) {
            $roles = $this->roleHierarchy->getReachableRoles($token->getRoles());
        } else {
            $roles = $token->getRoles();
        }

        $variables = [
            'token' => $token,
            'user' => $token->getUser(),
            'object' => $subject,
            'subject' => $subject,
            'roles' => array_map(function ($role) { return $role->getRole(); }, $roles),
            'trust_resolver' => $this->trustResolver,
            // "auth_checker" variable is used by Sensio security expressions (see \Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener::getVariables),
            // and would be available by default since Symfony 4.2 (see \Symfony\Component\Security\Core\Authorization\Voter\ExpressionVoter::getVariables).
            'auth_checker' => $this->authChecker,
        ];

        // this is mainly to propose a better experience when the expression is used
        // in an access control rule, as the developer does not know that it's going
        // to be handled by this voter
        if ($subject instanceof Request) {
            $variables['request'] = $subject;
        }

        return $variables;
    }
}
