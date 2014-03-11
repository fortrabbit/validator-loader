<?php
/**
 * This class is part of ValidatorRepository
 */

namespace Frbit\ValidatorLoader\Parser;

use Frbit\ValidatorLoader\Exception\InvalidValidatorStructureException;
use Frbit\ValidatorLoader\Parser;
use Illuminate\Validation\Factory;
use Symfony\Component\Translation\Translator;

/**
 * Class AbstractParser
 * @package Frbit\ValidatorLoader\Parser
 **/
class ValidatingCombinedParser implements Parser
{
    protected static $OUTER_VALIDATOR = array(
        array(
            'validators' => 'required|array',
            'variables'  => 'array',
            'methods'    => 'array'
        ), array(
            'validators.required' => '"validators" is missing',
            'validators.array'    => '"validators" is not an array',
            'variables'           => '"variables" is not an array',
            'methods'             => '"methods" is not an array'
        )
    );
    protected static $INNER_VALIDATOR = array(
        array(
            'rules'    => 'required|array',
            'messages' => 'array',
        ), array(
            'rules.required' => '"rules" is missing',
            'rules.array'    => '"rules" is not an array',
            'messages'       => '"messages" is not array',
        )
    );

    /**
     * @var Factory
     */
    protected $validatorFactory;

    /**
     * @param Parser  $parser
     * @param Factory $validatorFactory
     */
    public function __construct(Parser $parser, Factory $validatorFactory = null)
    {
        $this->parser = $parser;
        if (!$validatorFactory) {
            $translator       = new Translator('en');
            $validatorFactory = new Factory($translator);
        }
        $this->validatorFactory = $validatorFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($source)
    {
        $parsed = $this->parser->parse($source);

        return $this->validate($parsed);
    }

    /**
     * {@inheritdoc}
     */
    public function accepts($source)
    {
        return $this->parser->accepts($source);
    }

    /**
     * Takes parsed data, runs a quick validation and returns it
     *
     * @param array $parsed
     *
     * @throws InvalidValidatorStructureException
     * @return array
     */
    protected function validate(array $parsed)
    {
        $outerValidator = $this->validatorFactory->make($parsed, static::$OUTER_VALIDATOR[0], static::$OUTER_VALIDATOR[1]);
        if ($outerValidator->fails()) {
            $errors = $outerValidator->errors()->toArray();
            throw new InvalidValidatorStructureException('Invalid structure: ' . implode(' ** ', $errors['validators']));
        }

        foreach ($parsed['validators'] as $name => $definition) {
            $this->validateDefinition($definition, $name);
        }

        return $parsed;
    }

    /**
     * @param mixed  $definition
     * @param string $name
     *
     * @throws InvalidValidatorStructureException
     */
    protected function validateDefinition($definition, $name)
    {
        if (!is_array($definition)) {
            throw new InvalidValidatorStructureException("Definition of validator \"$name\" is not an array");
        }
        $innerValidator = $this->validatorFactory->make($definition, static::$INNER_VALIDATOR[0], static::$INNER_VALIDATOR[1]);
        if ($innerValidator->fails()) {
            $errors   = $innerValidator->errors()->toArray();
            $messages = array();
            foreach ($errors as $attribErrors) {
                $messages = array_merge($messages, $attribErrors);
            }
            throw new InvalidValidatorStructureException("Validator \"$name\" is invalid: " . implode(' ** ', $messages));
        }
    }

}