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
use Yarhon\RouteGuardBundle\ExpressionLanguage\DecoratedExpression;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DecoratedExpressionVoter extends Voter
{
    private $expressionLanguage;
    private $trustResolver;
    private $authChecker;
    private $roleHierarchy;

    public function __construct(ExpressionLanguage $expressionLanguage, AuthenticationTrustResolverInterface $trustResolver, AuthorizationCheckerInterface $authChecker, RoleHierarchyInterface $roleHierarchy = null)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->trustResolver = $trustResolver;
        $this->authChecker = $authChecker;
        $this->roleHierarchy = $roleHierarchy;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $attribute instanceof DecoratedExpression;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        /** @var DecoratedExpression $attribute */

        $variables = $this->getVariables($token, $subject);

        $expressionVariables = $attribute->getVariables();

        // TODO: do something with overlapped variables
        // $overlapped = $this->findOverlappedVariables($variables, $expressionVariables);

        // In case of overlap, built-in variables win.
        $variables = array_merge($expressionVariables, $variables);

        return $this->expressionLanguage->evaluate($attribute, $variables);
    }

    private function findOverlappedVariables(array $primary, array $secondary)
    {
        $overlapped = array_intersect(array_keys($primary), array_keys($secondary));
        foreach ($overlapped as $key => $variableName) {
            if ($primary[$variableName] === $secondary[$variableName]) {
                unset($overlapped[$key]);
            }
        }

        return $overlapped;
    }

    /**
     * @codeCoverageIgnore
     */
    private function getVariables(TokenInterface $token, $subject)
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
