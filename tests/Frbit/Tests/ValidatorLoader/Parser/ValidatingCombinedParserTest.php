<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\Tests\ValidatorLoader\Parser;

use Frbit\Tests\ValidatorLoader\TestCase;
use Frbit\ValidatorLoader\Parser\ValidatingCombinedParser;

/**
 * Not a strict unit tests. Needs to run the Laravel validator to verify validation rules are correct.
 *
 * @covers  \Frbit\ValidatorLoader\Parser\ValidatingCombinedParser
 * @package Frbit\Tests\ValidatorLoader\Parser
 **/
class ValidatingCombinedParserTest extends TestCase
{

    /**
     * @var \Mockery\MockInterface
     */
    protected $innerParser;

    /**
     * @var \Mockery\MockInterface
     */
    protected $validatorFactory;

    /**
     * @var array
     */
    protected $outerValidator;

    /**
     * @var array
     */
    protected $innerValidator;

    /**
     * @var array
     */
    protected $validatorData;

    public function setUp()
    {
        parent::setUp();
        $this->innerParser      = \Mockery::mock('Frbit\ValidatorLoader\Parser');
        $this->validatorFactory = \Mockery::mock('Illuminate\Validation\Factory');

        $this->outerValidator = array(
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
        $this->innerValidator = array(
            array(
                'rules'    => 'required|array',
                'messages' => 'array'
            ), array(
                'rules.required' => '"rules" is missing',
                'rules.array'    => '"rules" is not an array',
                'messages'       => '"messages" is not array',
            )
        );
        $this->validatorData  = array(
            'validators' => array(
                'name' => array(
                    'rules'    => array(
                        'parameter' => array(
                            'min:3',
                            'max:6',
                        )
                    ),
                    'messages' => array(
                        'parameter.min' => 'Too short',
                        'parameter.max' => 'Too long'
                    )
                )
            )
        );
    }

    public function testCreateInstance()
    {
        new ValidatingCombinedParser($this->innerParser);
        $this->assertTrue(true);
    }

    public function testDelegatesAccept()
    {
        $parser = $this->generateParser();
        $this->innerParser->shouldReceive('accepts')
            ->twice()
            ->with('source')
            ->andReturn(true, false);
        $this->assertTrue($parser->accepts('source'));
        $this->assertFalse($parser->accepts('source'));
    }

    public function testParserDelegatesAcceptsValidStructure()
    {

        $parser = $this->generateParser();

        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("source")
            ->andReturn($this->validatorData);

        $outerResult = \Mockery::mock('Illuminate\Validation\Validator');
        $this->validatorFactory->shouldReceive('make')
            ->once()
            ->with($this->validatorData, $this->outerValidator[0], $this->outerValidator[1])
            ->andReturn($outerResult);
        $outerResult->shouldReceive('fails')
            ->once()
            ->andReturn(false);

        $innerResult = \Mockery::mock('Illuminate\Validation\Validator');
        $this->validatorFactory->shouldReceive('make')
            ->once()
            ->with($this->validatorData['validators']['name'], $this->innerValidator[0], $this->innerValidator[1])
            ->andReturn($innerResult);
        $innerResult->shouldReceive('fails')
            ->once()
            ->andReturn(false);

        $result = $parser->parse('source');
        $this->assertEquals($this->validatorData, $result);
    }

    /**
     * @expectedException \Frbit\ValidatorLoader\Exception\InvalidValidatorStructureException
     * @expectedExceptionMessage Definition of validator "name" is not an array
     */
    public function testParserExceptionWithInvalidDefinition()
    {

        $parser = $this->generateParser();

        $data = $this->validatorData;
        $data['validators']['name'] = 'foo';
        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("source")
            ->andReturn($data);

        $outerResult = \Mockery::mock('Illuminate\Validation\Validator');
        $this->validatorFactory->shouldReceive('make')
            ->once()
            ->with($data, $this->outerValidator[0], $this->outerValidator[1])
            ->andReturn($outerResult);
        $outerResult->shouldReceive('fails')
            ->once()
            ->andReturn(false);



        $parser->parse('source');
    }

    /**
     * !! Not a strict unit tests!!
     *
     * Needs to run the validator to verify correct structure
     */
    public function testValidatorDoesNotThrowExceptionWithValidStructure()
    {

        $parser = $this->generateParser(false);

        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("source")
            ->andReturn($this->validatorData);

        $result = $parser->parse('source');
        $this->assertEquals($this->validatorData, $result);
    }

    /**
     * !! Not a strict unit tests!!
     *
     * Needs to run the validator to verify correct structure
     *
     * @expectedException \Frbit\ValidatorLoader\Exception\InvalidValidatorStructureException
     * @expectedExceptionMessage Invalid structure: "validators" is missing
     */
    public function testFailOnMissingValidators()
    {

        $parser = $this->generateParser(false);

        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("source")
            ->andReturn(['foo' => 'bar']);

        $parser->parse('source');
    }

    /**
     * !! Not a strict unit tests!!
     *
     * Needs to run the validator to verify correct structure
     *
     * @expectedException \Frbit\ValidatorLoader\Exception\InvalidValidatorStructureException
     * @expectedExceptionMessage Validator "name" is invalid: "rules" is missing
     */
    public function testFailOnMissingRulesInDefinition()
    {

        $parser = $this->generateParser(false);

        $data = $this->validatorData;
        unset($data['validators']['name']['rules']);
        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("source")
            ->andReturn($data);

        $parser->parse('source');
    }

    /**
     * !! Not a strict unit tests!!
     *
     * Needs to run the validator to verify correct structure
     *
     * @expectedException \Frbit\ValidatorLoader\Exception\InvalidValidatorStructureException
     * @expectedExceptionMessage Validator "name" is invalid: "rules" is not an array
     */
    public function testFailOnInvalidRulesInDefinition()
    {

        $parser = $this->generateParser(false);

        $data = $this->validatorData;
        $data['validators']['name']['rules'] = 'foo';
        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("source")
            ->andReturn($data);

        $parser->parse('source');
    }

    /**
     * @param bool $withMockValidator
     *
     * @return ValidatingCombinedParser
     */
    protected function generateParser($withMockValidator = true)
    {
        $parser = new ValidatingCombinedParser($this->innerParser, $withMockValidator ? $this->validatorFactory : null);

        return $parser;
    }

}