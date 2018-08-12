<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\RouteGuardBundle\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Yarhon\RouteGuardBundle\Exception\RuntimeException;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class ExpressionFactory implements ExpressionFactoryInterface
{
    /**
     * @var ExpressionLanguage;
     */
    private $expressionLanguage;

    /**
     * @var bool
     */
    private $useExpressionLanguageCache;

    /**
     * @var int
     */
    private $versionId;

    /**
     * @param ExpressionLanguage|null $expressionLanguage
     * @param bool|null               $useExpressionLanguageCache
     * @param int|null                $versionId
     */
    public function __construct(ExpressionLanguage $expressionLanguage = null, $useExpressionLanguageCache = null, $versionId = null)
    {
        $this->expressionLanguage = $expressionLanguage;

        // Prior to Symfony 4.1 "security.expression_language" service doesn't uses persistent cache adapter,
        // so we should create ParsedExpression instance to avoid parsing at runtime (at ExpressionLanguage::evaluate call)
        //
        // Starting from Symfony 4.1 parsed expression would be saved in "security.expression_language" service persistent cache,
        // so we can simply create Expression instance and rely on service cache (caching would be triggered by ExpressionLanguage::parse call).
        // In this case it's significant to provide exactly the same names list both to the ExpressionLanguage::parse
        // and ExpressionLanguage::evaluate calls, as names list is a part of the cache key.

        if (null === $useExpressionLanguageCache && null !== $versionId) {
            $useExpressionLanguageCache = $versionId >= 40100;
        }

        $this->useExpressionLanguageCache = $useExpressionLanguageCache;
        $this->versionId = $versionId;
    }

    /**
     * {@inheritdoc}
     */
    public function create($expression, array $names = [])
    {
        if (!$this->expressionLanguage) {
            throw new RuntimeException('Can\'t create an Expression as ExpressionLanguage is not provided.');
        }

        $expression = new Expression($expression);

        // TODO: this would not work properly with string keys (string keys can be used for renaming variables in compiled code).
        // See \Symfony\Component\ExpressionLanguage\Parser::parse
        $names = array_merge($this->getDefaultVariables(), $names);

        $parsedExpression = $this->expressionLanguage->parse($expression, $names);

        //$ast = $parsedExpression->getNodes();

        if (!$this->useExpressionLanguageCache) {
            return $parsedExpression;
        }

        return $expression;
    }

    /**
     * @see \Symfony\Component\Security\Core\Authorization\Voter\ExpressionVoter::getVariables
     *
     * @return array
     */
    protected function getDefaultVariables()
    {
        $names = ['token', 'user', 'object', 'subject', 'roles', 'trust_resolver'];

        // TODO: check if it would be released in 4.2
        if (null !== $this->versionId && $this->versionId >= 40200) {
            $names[] = ['auth_checker'];
        }

        return $names;
    }
}
