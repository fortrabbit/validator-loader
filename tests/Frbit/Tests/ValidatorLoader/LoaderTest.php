<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\Tests\ValidatorLoader;

use Frbit\ValidatorLoader\Loader;

/**
 * @covers  \Frbit\ValidatorLoader\Loader
 * @package Frbit\Tests\ValidatorLoader
 **/
class LoaderTest extends TestCase
{

    /**
     * @var \Mockery\MockInterface
     */
    protected $validatorFactory;

    public function setUp()
    {
        parent::setUp();
        $this->validatorFactory = \Mockery::mock('Illuminate\Validation\Factory');
    }

    public function testCreateFromDefinition()
    {
        new Loader(array());
        $this->assertTrue(true);
    }

    public function testSetMethodsFromCreate()
    {
        $loader = $this->generateLoader(array(
            'methods' => array(
                'foo' => 'bar'
            )
        ));
        $this->assertAttributeEquals(array('foo' => 'bar'), 'methods', $loader);
    }

    public function testSetValidatorsFromCreate()
    {
        $loader = $this->generateLoader(array(
            'validators' => array(
                'foo' => 'bar'
            )
        ));
        $this->assertAttributeEquals(array('foo' => 'bar'), 'validators', $loader);
    }

    public function testSetMethods()
    {
        $loader = $this->generateLoader();
        $loader->setMethods(array(
            'foo' => 'bar'
        ));
        $this->assertAttributeEquals(array('foo' => 'bar'), 'methods', $loader);
    }

    public function testSetValidators()
    {
        $loader = $this->generateLoader();
        $loader->setValidators(array(
            'foo' => 'bar'
        ));
        $this->assertAttributeEquals(array('foo' => 'bar'), 'validators', $loader);
    }

    public function testAddSingleMethod()
    {
        $loader = $this->generateLoader();
        $loader->setMethod('foo', 'bar');
        $this->assertAttributeEquals(array('foo' => 'bar'), 'methods', $loader);
    }

    public function testSetSingleValidator()
    {
        $loader = $this->generateLoader();
        $loader->setValidator('foo', array('bar'));
        $this->assertAttributeEquals(array('foo' => array('bar')), 'validators', $loader);
    }

    public function testGetExistingValidator()
    {
        $loader = $this->generateLoader(array(
            'validators' => array(
                'foo' => array(
                    'rules'    => array(
                        'parameter' => array('min:3')
                    ),
                    'messages' => array(
                        'parameter' => 'Too short'
                    )
                )
            )
        ));

        $validator = \Mockery::mock('Illuminate\Validation\Validator');
        $this->validatorFactory->shouldReceive('make')
            ->once()
            ->with(array('parameter' => 'foo'), array('parameter' => array('min:3')), array('parameter' => 'Too short'))
            ->andReturn($validator);

        $result = $loader->get('foo', array('parameter' => 'foo'));
        $this->assertSame($validator, $result);
    }

    public function testRegisteredMethodsAreAddedToGeneratedValidator()
    {
        $loader = $this->generateLoader(array(
            'validators' => array(
                'foo' => array(
                    'rules'    => array(
                        'parameter' => array('min:3')
                    ),
                    'messages' => array(
                        'parameter' => 'Too short'
                    )
                )
            ),
            'methods'    => array(
                'foo' => 'Foo',
                'bar' => 'Bazoing'
            )
        ));

        $validator = \Mockery::mock('Illuminate\Validation\Validator');
        $this->validatorFactory->shouldReceive('make')
            ->once()
            ->with(array('parameter' => 'foo'), array('parameter' => array('min:3')), array('parameter' => 'Too short'))
            ->andReturn($validator);

        $validator->shouldReceive('addExtension')
            ->once()
            ->with('foo', 'Foo');
        $validator->shouldReceive('addExtension')
            ->once()
            ->with('bar', 'Bazoing');

        $result = $loader->get('foo', array('parameter' => 'foo'));
        $this->assertSame($validator, $result);
    }

    /**
     * @expectedException \Frbit\ValidatorLoader\Exception\UnknownValidatorException
     * @expectedExceptionMessage Unknown validator "foo"
     */
    public function testFailWhenNotRegisteredValidatorIsRequested()
    {
        $loader = $this->generateLoader();
        $loader->get('foo', array('parameter' => 'foo'));
    }

    public function testMakingArrayFromLoader()
    {
        $loader = $this->generateLoader(array(
            'validators' => array(
                'foo' => array(
                    'rules'    => array(
                        'parameter' => array('min:3')
                    ),
                    'messages' => array(
                        'parameter' => 'Too short'
                    )
                )
            ),
            'methods'    => array(
                'foo' => 'Foo',
                'bar' => 'Bazoing'
            )
        ));

        $this->assertEquals(array(
            'validators' => array(
                'foo' => array(
                    'rules'    => array(
                        'parameter' => array('min:3')
                    ),
                    'messages' => array(
                        'parameter' => 'Too short'
                    )
                )
            ),
            'methods'    => array(
                'foo' => 'Foo',
                'bar' => 'Bazoing'
            )
        ), $loader->toArray());
    }

    public function testMakingArrayFromEmptyLoader()
    {
        $loader = $this->generateLoader(array());

        $this->assertEquals(array(
            'validators' => array(),
            'methods'    => array(),
        ), $loader->toArray());
    }

    protected function generateLoader(array $validators = array())
    {
        return new Loader($validators, $this->validatorFactory);
    }

}