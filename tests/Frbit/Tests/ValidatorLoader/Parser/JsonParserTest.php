<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\Tests\ValidatorLoader\Parser;

use Frbit\Tests\ValidatorLoader\TestCase;
use Frbit\ValidatorLoader\Parser\JsonParser;

/**
 * @covers  \Frbit\ValidatorLoader\Parser\JsonParser
 * @package Frbit\Tests\ValidatorLoader\Parser
 **/
class JsonParserTest extends TestCase
{

    /**
     * @var string
     */
    protected $source;

    public function setUp()
    {
        parent::setUp();
        $this->source = __DIR__ . '/../Fixtures/validator.json';
    }

    public function testCreateInstance()
    {
        new JsonParser();
        $this->assertTrue(true);
    }

    public function testAcceptsJsonFile()
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
            ->with('not-existing-file.json')
            ->andReturn(false);
        $this->assertFalse($parser->accepts('not-existing-file.json'));
    }

    public function testDoesNotAcceptNonJsonFile()
    {
        $parser = $this->generateParser();
        $this->fileSystem->shouldReceive('isFile')
            ->once()
            ->with('existing-file.foo')
            ->andReturn(true);
        $this->assertFalse($parser->accepts('existing-file.foo'));
    }


    public function testParsesJsonFileSuccessfully()
    {
        $parser = $this->generateParser();

        $this->fileSystem->shouldReceive('get')
            ->once()
            ->with($this->source)
            ->andReturn($this->jsonContent());

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

    protected function jsonContent()
    {
        return <<<JSON
{
    "validators": {
        "name": {
            "rules": {
                "parameter": [
                    "min:3",
                    "max:6"
                ]
            },
            "messages": {
                "parameter.min": "Too short",
                "parameter.max": "Too long"
            }
        }
    }
}
JSON;
    }

    /**
     * @return JsonParser
     */
    protected function generateParser()
    {
        $parser = new JsonParser($this->fileSystem);

        return $parser;
    }

} 