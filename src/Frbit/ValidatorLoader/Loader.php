<?php
/**
 * This class is part of BackendApi
 */

namespace Frbit\ValidatorLoader;

use Frbit\ValidatorLoader\Exception\UnknownValidatorException;
use Illuminate\Validation\Factory as ValidatorFactory;
use Illuminate\Validation\Validator;
use Symfony\Component\Translation\Translator;

/**
 * Loader handles access to validators
 *
 * @package Frbit\ApiValidation
 **/
class Loader
{
    /**
     * @var array
     */
    protected $validators;

    /**
     * @var array
     */
    protected $customMethods;

    /**
     * @var ValidatorFactory
     */
    protected $validatorFactory;


    /**
     * @param array            $definition
     * @param ValidatorFactory $validatorFactory
     */
    public function __construct(array $definition, ValidatorFactory $validatorFactory = null)
    {
        if (!$validatorFactory) {
            $translator       = new Translator('en');
            $validatorFactory = new ValidatorFactory($translator);
        }
        $this->validatorFactory = $validatorFactory;
        $this->methods          = array();
        $this->validators       = array();
        $this->initDefinition($definition);
    }

    /**
     * @param string $name
     *
     * @param array  $inputData
     *
     * @throws Exception\UnknownValidatorException
     * @return Validator
     */
    public function get($name, array $inputData)
    {
        if (!isset($this->validators[$name])) {
            throw new UnknownValidatorException("Unknown validator \"$name\"");
        }

        $rules     = isset($this->validators[$name]['rules']) ? $this->validators[$name]['rules'] : array();
        $messages  = isset($this->validators[$name]['messages']) ? $this->validators[$name]['messages'] : array();
        $validator = $this->validatorFactory->make($inputData, $rules, $messages);


        foreach ($this->methods as $name => $callback) {
            $validator->addExtension($name, $callback);
        }

        return $validator;
    }

    /**
     * @param string $name
     * @param string $callback see: http://laravel.com/docs/validation#custom-validation-rules (extend method)
     */
    public function setMethod($name, $callback)
    {
        $this->methods[$name] = $callback;
    }

    /**
     * Set methods
     *
     * @param array $methods
     */
    public function setMethods(array $methods)
    {
        $this->methods = $methods;
    }

    /**
     * Set a named validator
     *
     * $definition has the form:
     *  array(
     *      'rules'    => array(..),
     *      'messages' => array(..),
     *  )
     *
     * @param string $name
     * @param array  $definition
     */
    public function setValidator($name, array $definition)
    {
        $this->validators[$name] = $definition;
    }

    /**
     * Set validators
     *
     * @param array $validators
     */
    public function setValidators(array $validators)
    {
        $this->validators = $validators;
    }

    /**
     * Init from full definition
     *
     * @param array $definition
     */
    protected function initDefinition(array $definition)
    {
        if (isset($definition['methods'])) {
            $this->setMethods($definition['methods']);
        }
        if (isset($definition['validators'])) {
            $this->setValidators($definition['validators']);
        }
    }

}