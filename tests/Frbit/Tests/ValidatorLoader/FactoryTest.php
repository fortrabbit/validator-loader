<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\Tests\ValidatorLoader;

use Frbit\ValidatorLoader\Factory;

/**
 * Cannot be a strict unit test.. so isn't
 *
 * @covers  \Frbit\ValidatorLoader\Factory
 * @package Frbit\Tests\ValidatorLoader
 **/
class FactoryTest extends TestCase
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

    public function testCreateFromFile()
    {
        $this->fileSystem->shouldReceive('isFile')
            ->with('foo.json')
            ->andReturn(true);
        $this->fileSystem->shouldReceive('get')
            ->with('foo.json')
            ->andReturn('{"validators":{"foo":{"rules":{"parameter":["min:3"]}}}}');

        $loader = Factory::fromFile('foo.json', $this->validatorFactory, $this->fileSystem);

        $this->assertAttributeEquals(array(
            'foo' => array(
                'rules' => array(
                    'parameter' => array('min:3')
                )
            )
        ), 'validators', $loader);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Source file "foo.json" seems does not exist or is not accessible
     */
    public function testFailsCreateFromFileWithNotExistingFile()
    {
        $this->fileSystem->shouldReceive('isFile')
            ->with('foo.json')
            ->andReturn(false);

        Factory::fromFile('foo.json', $this->validatorFactory, $this->fileSystem);
    }

    public function testCreateFromDirectory()
    {
        $this->fileSystem->shouldReceive('isDirectory')
            ->with('foo')
            ->andReturn(true);
        $this->fileSystem->shouldReceive('glob')
            ->with('foo/*')
            ->andReturn(array("foo/bar.json"));
        $this->fileSystem->shouldReceive('isFile')
            ->with('foo/bar.json')
            ->andReturn(true);
        $this->fileSystem->shouldReceive('get')
            ->with('foo/bar.json')
            ->andReturn('{"validators":{"foo":{"rules":{"parameter":["min:3"]}}}}');

        $loader = Factory::fromDirectory('foo', false, $this->validatorFactory, $this->fileSystem);

        $this->assertAttributeEquals(array(
            'foo' => array(
                'rules' => array(
                    'parameter' => array('min:3')
                )
            )
        ), 'validators', $loader);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Source directory "foo" seems does not exist or is not accessible
     */
    public function testFailsCreateFromDirectoryWhichIsNotExisting()
    {
        $this->fileSystem->shouldReceive('isDirectory')
            ->with('foo')
            ->andReturn(false);

        Factory::fromDirectory('foo', false, $this->validatorFactory, $this->fileSystem);
    }

    public function testCreateFromArray()
    {
        $loader = Factory::fromArray(array(
            'validators' => array(
                'foo' => array(
                    'rules' => array(
                        'parameter' => array('min:3')
                    )
                )
            )
        ), $this->validatorFactory, $this->fileSystem);

        $this->assertAttributeEquals(array(
            'foo' => array(
                'rules' => array(
                    'parameter' => array('min:3')
                )
            )
        ), 'validators', $loader);
    }

    public function testCreateFromCustom()
    {
        $validators = array(
            'validators' => array(
                'foo' => array(
                    'rules' => array(
                        'parameter' => array('min:3')
                    )
                )
            )
        );
        $parser     = \Mockery::mock('Frbit\ValidatorLoader\Parser');

        $parser->shouldReceive('parse')
            ->once()
            ->with($validators)
            ->andReturn($validators);

        $loader = Factory::fromCustom($validators, $parser, $this->validatorFactory, $this->fileSystem);

        $this->assertAttributeEquals($validators['validators'], 'validators', $loader);
    }

}