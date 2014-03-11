<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\Tests\ValidatorLoader\Parser;

use Frbit\Tests\ValidatorLoader\TestCase;
use Frbit\ValidatorLoader\Parser\YamlParser;

/**
 * @covers  \Frbit\ValidatorLoader\Parser\YamlParser
 * @package Frbit\Tests\ValidatorLoader\Parser
 **/
class YamlParserTest extends TestCase
{

    /**
     * @var string
     */
    protected $source;

    /**
     * @var \Mockery\MockInterface
     */
    protected $yaml;

    public function setUp()
    {
        parent::setUp();
        $this->source = __DIR__ . '/../Fixtures/validator.yml';
        $this->yaml   = \Mockery::mock('Symfony\Component\Yaml\Yaml');
    }

    public function testCreateInstance()
    {
        new YamlParser();
        $this->assertTrue(true);
    }

    public function testAcceptsYamlFile()
    {
        $parser = $this->generateParser();
        $this->fileSystem->shouldReceive('isFile')
            ->once()
            ->with($this->source)
            ->andReturn(true);
        $this->assertTrue($parser->accepts($this->source));
    }

    public function testDoesNotAcceptNotExistingSource()
    {
        $parser = $this->generateParser();
        $this->fileSystem->shouldReceive('isFile')
            ->once()
            ->with('not-existing-file.yml')
            ->andReturn(false);
        $this->assertFalse($parser->accepts('not-existing-file.yml'));
    }

    public function testDoesNotAcceptNonYamlFile()
    {
        $parser = $this->generateParser();
        $this->fileSystem->shouldReceive('isFile')
            ->once()
            ->with('existing-file.foo')
            ->andReturn(true);
        $this->assertFalse($parser->accepts('existing-file.foo'));
    }


    public function testParsesYamlFileSuccessfully()
    {
        $parser = $this->generateParser();

        $this->yaml->shouldReceive('parse')
            ->once()
            ->with($this->source)
            ->andReturn(array(
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
            ));

        $result = $parser->parse($this->source);
        $this->assertEquals(array(
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
        ), $result);
    }

    protected function yamlContent()
    {
        return <<<YAML
---
validators:
    name:
        rules:
            parameter:
                - min:3
                - max:6
        messages:
            parameter.min: Too short
            parameter.max: Too long


YAML;
    }

    /**
     * @return YamlParser
     */
    protected function generateParser()
    {
        $parser = new YamlParser($this->yaml, $this->fileSystem);

        return $parser;
    }

}