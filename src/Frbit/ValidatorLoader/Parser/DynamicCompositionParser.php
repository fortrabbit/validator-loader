<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\ValidatorLoader\Parser;

use Frbit\ValidatorLoader\Exception\ImpossibleExtensionException;
use Frbit\ValidatorLoader\Exception\MissingVariableException;
use Frbit\ValidatorLoader\Parser;


/**
 * Class DynamicCompositionParser
 * @package Frbit\ValidatorLoader\Parser
 **/
class DynamicCompositionParser implements Parser
{
    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var array
     */
    protected $variables;

    /**
     * @var array
     */
    protected $validators;

    /**
     * @var string
     */
    protected $extendsDirective;

    /**
     * @param Parser $parser
     */
    public function __construct(Parser $parser)
    {
        $this->parser           = $parser;
        $this->extendsDirective = 'extends';
    }

    /**
     * Replace the default verb "extends" with a different key
     *
     * @param string $extendsDirective
     */
    public function setExtendsDirective($extendsDirective)
    {
        $this->extendsDirective = $extendsDirective;
    }

    /**
     * Takes source and parses content into array. Then resolves variables and implements inheritance and returns updated data.
     *
     * @param string $source
     *
     * @return array
     */
    public function parse($source)
    {
        $parsed           = $this->parser->parse($source);
        $this->variables  = isset($parsed['variables']) ? $parsed['variables'] : array();
        $this->validators = isset($parsed['validators']) ? $parsed['validators'] : array();
        $this->resolveVariables();
        $this->resolveInheritance();

        $parsed['validators'] = $this->validators;
        if (isset($parsed['variables'])) {
            $parsed['variables'] = $this->variables;
        }

        return $parsed;
    }

    /**
     * {@inheritdoc}
     */
    public function accepts($source)
    {
        return $this->parser->accepts($source);
    }

    protected function resolveInheritance()
    {
        foreach (array_keys($this->validators) as $validatorName) {
            $this->extendValidator($validatorName);
        }
    }

    protected function extendValidator($validatorName)
    {
        $current = & $this->validators[$validatorName];

        // no need?
        if (!isset($current[$this->extendsDirective])) {
            return;
        }

        // cleanup extends to prohibit infinite recursion
        $extends = (array)$current[$this->extendsDirective];
        unset($current[$this->extendsDirective]);

        // implement all extensions
        foreach ($extends as $extension) {

            // oops
            if (!isset($this->validators[$extension])) {
                throw new ImpossibleExtensionException("Could not extend \"$validatorName\" with \"$extension\" because \"$extension\" does not exist");
            }

            // extend only from extended
            $this->extendValidator($extension);

            // inherit rules and messages
            $parent = $this->validators[$extension];
            static::extendValidatorRules($parent, $current);
            static::extendValidatorMessages($parent, $current);
        }
    }

    /**
     * @param array $parent
     * @param array $current
     */
    protected static function extendValidatorRules(array $parent, array &$current)
    {
        if (!isset($parent['rules'])) {
            return;
        }
        if (!isset($current['rules'])) {
            $current['rules'] = array();
        }
        foreach ($parent['rules'] as $parameter => $rules) {
            if (!isset($current['rules'][$parameter])) {
                $current['rules'][$parameter] = $rules;
            } else {
                $currentRulesByMethod = static::extractRulesByMethod($current['rules'][$parameter]);
                $parentRulesByMethod  = static::extractRulesByMethod($rules);
                foreach ($parentRulesByMethod as $ruleName => $rule) {
                    if (!isset($currentRulesByMethod[$ruleName])) {
                        $current['rules'][$parameter][] = $rule;
                    }
                }
            }
        }
    }

    /**
     * @param array $rules
     *
     * @return array
     */
    protected static function extractRulesByMethod(array $rules)
    {
        $rulesByMethod = array();
        foreach ($rules as $rule) {
            if (strpos($rule, ':') === false) {
                $rulesByMethod[$rule] = $rule;
            } else {
                list($ruleName,) = explode(':', $rule, 2);
                $rulesByMethod[$ruleName] = $rule;
            }
        }

        return $rulesByMethod;
    }

    /**
     * @param array $parent
     * @param array $current
     */
    protected static function extendValidatorMessages(array $parent, array &$current)
    {
        if (!isset($parent['messages'])) {
            return;
        }
        foreach ($parent['messages'] as $ruleKey => $definition) {
            if (!isset($current['messages'][$ruleKey])) {
                $current['messages'][$ruleKey] = $definition;
            }
        }
    }

    /**
     * Resolves all variables
     */
    protected function resolveVariables()
    {
        foreach ($this->variables as $key => $value) {
            $this->variables[$key] = static::replaceAllVariables($value, $this->variables, 'variables', 'GLOBAL VARIABLES');
        }
        foreach ($this->validators as $validatorName => &$validator) {
            foreach (array('rules', 'messages') as $context) {
                if (!isset($validator[$context])) {
                    continue;
                }
                foreach ($validator[$context] as $key => $value) {
                    $validator[$context][$key] = static::replaceAllVariables($value, $this->variables, $context, $validatorName);
                }
            }
        }
    }

    /**
     * Replaces all varaiables in string
     *
     * @param string $value
     * @param array  $variables
     * @param string $context
     * @param string $validator
     *
     * @return mixed
     */
    protected static function replaceAllVariables($value, array $variables, $context, $validator)
    {
        return preg_replace_callback('#<<([^>]+)>>#', function ($match) use ($variables, $context, $validator) {
            $variable = $match[1];
            if (!isset($variables[$variable])) {
                throw new MissingVariableException("Could not find variable \"$variable\" for \"$context\" of \"$validator\"");
            }

            return $variables[$variable];
        }, $value);
    }

}