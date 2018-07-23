<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yarhon\LinkGuardBundle\Twig\Node;

use Twig\Node\Node;
use Twig\Error\SyntaxError;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Expression\ConstantExpression;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteExpression extends FunctionExpression
{
    /**
     * RouteExpression constructor.
     *
     * @param Node $arguments
     * @param int  $line
     *
     * @throws SyntaxError
     */
    public function __construct(Node $arguments, $line = 0)
    {
        // TODO: validate arguments types?

        if ($arguments->count() < 1) {
            throw new SyntaxError('At least one argument (name) is required.', $line);
        }

        if ($arguments->count() > 3) {
            throw new SyntaxError('Unrecognized extra arguments, only 3 (name, parameters, method) allowed.', $line);
        }

        $this->addDefaultArguments($arguments);

        parent::__construct(null, $arguments, $line);

        // Set default generateAs parameters.
        // We call this functions after parent constructor call to allow them to rely on internal structure
        // created by parent constructor (i.e., call $this->getNode('arguments'), $this->getTemplateLine()).
        $this->setFunctionName('path');
        $this->setRelative(false);
    }

    // TODO: allow to specify $generateAs as a constant (one of UrlGeneratorInterface constants).
    // Note that $relative parameter in setRelative() method can be an instance of AbstractExpression,
    // and it's execution result can be non-calculable at compile time.

    /**
     * @param array $generateAs
     *
     * @return self
     *
     * @throws SyntaxError
     */
    public function setGenerateAs($generateAs)
    {
        if (is_array($generateAs)) {
            if (!isset($generateAs[0])) {
                throw new SyntaxError('setGenerateAs array parameter must have at least one parameter (functionName).',
                    $this->getTemplateLine());
            }

            $this->setFunctionName($generateAs[0]);

            if (isset($generateAs[1])) {
                $this->setRelative($generateAs[1]);
            }
        }

        return $this;
    }

    /**
     * @param string $functionName
     *
     * @return self
     *
     * @throws SyntaxError
     */
    public function setFunctionName($functionName)
    {
        $functionName = $this->transformFunctionName($functionName);
        $this->setAttribute('name', $functionName);

        return $this;
    }

    /**
     * @param bool|AbstractExpression $relative
     *
     * @return self
     */
    public function setRelative($relative)
    {
        // TODO: validate $relative

        if (!($relative instanceof AbstractExpression)) {
            $relative = new ConstantExpression($relative, $this->getTemplateLine());
        }

        $this->getNode('arguments')->setNode(3, $relative);

        return $this;
    }

    /**
     * @param string $functionName
     *
     * @return string
     *
     * @throws SyntaxError
     */
    private function transformFunctionName($functionName)
    {
        if ('path' == $functionName) {
            return 'path_if_granted';
        } elseif ('url' == $functionName) {
            return 'url_if_granted';
        } else {
            throw new SyntaxError(sprintf('Invalid function name: %s', $functionName), $this->getTemplateLine());
        }
    }

    /**
     * @param Node $arguments
     */
    private function addDefaultArguments(Node $arguments)
    {
        $line = $arguments->getTemplateLine();

        if (1 == $arguments->count()) {
            // add a default "parameters" argument
            $arguments->setNode(1, new ArrayExpression([], $line));
        }

        if (2 == $arguments->count()) {
            // add a default "method" argument
            $arguments->setNode(2, new ConstantExpression('GET', $line));
        }
    }
}
