<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\Tests\ValidatorLoader\Parser;

use Frbit\Tests\ValidatorLoader\TestCase;
use Frbit\ValidatorLoader\Parser\PhpParser;

/**
 * @covers  \Frbit\ValidatorLoader\Parser\PhpParser
 * @package Frbit\Tests\ValidatorLoader\Parser
 **/
class PhpParserTest extends TestCase
{

    /**
     * @var string
     */
    protected $source;

    public function setUp()
    {
        parent::setUp();
        $this->source = __DIR__ . '/../Fixtures/validator.php';
    }

    public function testCreateInstance()
    {
        new PhpParser();
        $this->assertTrue(true);
    }

    public function testAcceptsPhpFile()
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
            ->with('not-existing-file.php')
            ->andReturn(false);
        $this->assertFalse($parser->accepts('not-existing-file.php'));
    }

    public function testDoesNotAcceptNonPhpFile()
    {
        $parser = $this->generateParser();
        $this->fileSystem->shouldReceive('isFile')
            ->once()
            ->with('existing-file.json')
            ->andReturn(true);
        $this->assertFalse($parser->accepts('existing-file.json'));
    }


    public function testParsesPhpFileSuccessfully()
    {
        $parser = $this->generateParser();
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

    /**
     * @return PhpParser
     */
    protected function generateParser()
    {
        $parser = new PhpParser($this->fileSystem);

        return $parser;
    }

} 