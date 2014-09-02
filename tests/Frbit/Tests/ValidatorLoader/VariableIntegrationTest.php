<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\Tests\ValidatorLoader;

use Frbit\ValidatorLoader\Factory;

/**
 * @covers  \Frbit\ValidatorLoader\Loader
 * @package Frbit\Tests\ValidatorLoader
 **/
class VariableIntegrationTest extends TestCase
{

    /**
     * @var string
     */
    protected $source;

    public function setUp()
    {
        parent::setUp();
        $this->source = __DIR__ . '/Fixtures/combined';
    }

    public function testVariablesAreReplacedInVariables()
    {
        $loader = Factory::fromDirectory($this->source);
        $this->assertAttributeEquals(array(
            'bar' => array(
                'rules'    => array(
                    'parameter' => array(
                        'required',
                    ),
                ),
                'messages' => array(
                    'parameter.required' => 'Text would be from foo one "This is foo one"',
                ),
            ),
            'baz' => array(
                'rules'    => array(
                    'parameter' => array(
                        'required',
                    ),
                ),
                'messages' => array(
                    'parameter.required' => 'Combined: This combines foo one and bar one: This is foo one, This is bar one',
                ),
            ),
            'foo' => array(
                'rules'    => array(
                    'parameter' => array(
                        'required',
                    ),
                ),
                'messages' => array(
                    'parameter.required' => 'Got text delegated "Foo two gets "This is bar one""',
                ),
            ),
        ), 'validators', $loader);
    }

}