<?php

/*
 *
 * (c) Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NeonLight\SecureLinksBundle\Twig\Node;

use Twig\Node\Node;
use Twig\Error\SyntaxError;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Expression\ConstantExpression;

/**
 * @author Yaroslav Honcharuk <yaroslav.xs@gmail.com>
 */
class RouteIfGrantedExpression extends FunctionExpression
{
    /**
     * RouteIfGrantedExpression constructor.
     *
     * @param Node $arguments
     * @param int $line
     *
     * @throws SyntaxError
     */
    public function __construct(Node $arguments, $line = 0)
    {
        // TODO: validate arguments types?

        if ($arguments->count() < 1) {
            throw new SyntaxError("At least one argument (name) is required.", $line);
        }

        if ($arguments->count() > 3) {
            throw new SyntaxError("Unrecognized extra arguments, only 3 (name, parameters, method) allowed.", $line);
        }

        $this->addDefaultArguments($arguments, $line);

        parent::__construct(null, $arguments, $line);

        // Set default generate parameters.
        // We call this function after parent constructor call to allow them to rely on internal structure
        // created by parent constructor (i.e., call $this->getNode('arguments'), $this->getTemplateLine()).
        $this->setFunctionName('path');
        $this->setRelative(false);
    }

    /**
     * @param $functionName
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
        if ($functionName == 'path') {
            return 'path_if_granted';
        } elseif ($functionName == 'url') {
            return 'url_if_granted';
        } else {
            throw new SyntaxError(sprintf('Invalid function name: %s', $functionName), $this->getTemplateLine());
        }
    }

    private function addDefaultArguments(Node $arguments, $line = 0)
    {
        if ($arguments->count() == 1) {
            // add a default "parameters" argument
            $arguments->setNode(1, new ArrayExpression([], $line));
        }

        if ($arguments->count() == 2) {
            // add a default "method" argument
            $arguments->setNode(2, new ConstantExpression('GET', $line));
        }
    }
}