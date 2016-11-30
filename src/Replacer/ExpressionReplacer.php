<?php

namespace Isometriks\JsonLdDumper\Replacer;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ExpressionReplacer implements ReplacerInterface
{
    private $expressionLanguage;

    public function __construct(ExpressionLanguage $expressionLanguage)
    {
        $this->expressionLanguage = $expressionLanguage;
    }

    public function canParse($value, $context = null)
    {
        if (!is_string($value)) {
            return false;
        }

        return substr($value, 0, 5) === 'expr:';
    }

    public function replace($value, $context = null)
    {
        $expression = substr($value, 5);
        $result = $this->expressionLanguage->evaluate($expression, array(
            'context' => $context
        ));

        return new ReturnValue($result, false);
    }
}
