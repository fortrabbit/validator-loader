<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\Tests\ValidatorLoader\Parser;

use Frbit\Tests\ValidatorLoader\TestCase;
use Frbit\ValidatorLoader\Parser\DirectoryCompositionParser;


/**
 * @covers  \Frbit\ValidatorLoader\Parser\DirectoryCompositionParser
 * @package Frbit\Tests\ValidatorLoader\Parser
 **/
class DirectoryCompositionParserTest extends TestCase
{

    /**
     * @var string
     */
    protected $source;

    /**
     * @var \Mockery\MockInterface
     */
    protected $innerParser;

    public function setUp()
    {
        parent::setUp();
        $this->source      = __DIR__ . '/../Fixtures/combined';
        $this->innerParser = \Mockery::mock('Frbit\ValidatorLoader\Parser');
    }

    public function testCreateInstance()
    {
        new DirectoryCompositionParser($this->innerParser);
        $this->assertTrue(true);
    }

    public function testAcceptsDirectory()
    {
        $parser = $this->generateParser();
        $this->fileSystem->shouldReceive('isDirectory')
            ->once()
            ->with($this->source)
            ->andReturn(true);
        $this->assertTrue($parser->accepts($this->source));
    }

    public function testDoesNotAcceptNonDirectorySource()
    {
        $parser = $this->generateParser();
        $this->fileSystem->shouldReceive('isDirectory')
            ->once()
            ->with($this->source)
            ->andReturn(false);
        $this->assertFalse($parser->accepts($this->source));
    }


    public function testParsesDirectoryWithSingleFile()
    {
        $parser = $this->generateParser();

        $this->fileSystem->shouldReceive('glob')
            ->once()
            ->with("directory/*")
            ->andReturn(array(
                "directory/file1",
            ));

        $this->innerParser->shouldReceive('accepts')
            ->once()
            ->with("directory/file1")
            ->andReturn(true);

        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("directory/file1")
            ->andReturn($this->generateValidatorData('name', 'parameter'));

        $result = $parser->parse('directory');
        $this->assertEquals(array(
            'validators' => array(
                'file1.name' => array(
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


    public function testParsesDirectoryWithMultipleFiles()
    {
        $parser = $this->generateParser();

        $this->fileSystem->shouldReceive('glob')
            ->once()
            ->with("directory/*")
            ->andReturn(array(
                "directory/file1",
                "directory/file2",
                "directory/file3",
                "directory/file4",
            ));

        $this->innerParser->shouldReceive('accepts')
            ->once()
            ->with("directory/file1")
            ->andReturn(true);
        $this->innerParser->shouldReceive('accepts')
            ->once()
            ->with("directory/file2")
            ->andReturn(true);
        $this->innerParser->shouldReceive('accepts')
            ->once()
            ->with("directory/file3")
            ->andReturn(true);
        $this->innerParser->shouldReceive('accepts')
            ->once()
            ->with("directory/file4")
            ->andReturn(false);

        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("directory/file1")
            ->andReturn($this->generateValidatorData('name1', 'parameter1'));
        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("directory/file2")
            ->andReturn($this->generateValidatorData('name2', 'parameter2'));
        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("directory/file3")
            ->andReturn($this->generateValidatorData('name1', 'parameter3'));

        $result = $parser->parse('directory');
        $this->assertEquals(array(
            'validators' => array(
                'file1.name1' => array(
                    'rules'    => array(
                        'parameter1' => array(
                            'min:3',
                            'max:6',
                        )
                    ),
                    'messages' => array(
                        'parameter1.min' => 'Too short',
                        'parameter1.max' => 'Too long'
                    )
                ),
                'file2.name2' => array(
                    'rules'    => array(
                        'parameter2' => array(
                            'min:3',
                            'max:6',
                        )
                    ),
                    'messages' => array(
                        'parameter2.min' => 'Too short',
                        'parameter2.max' => 'Too long'
                    )
                ),
                'file3.name1' => array(
                    'rules'    => array(
                        'parameter3' => array(
                            'min:3',
                            'max:6',
                        )
                    ),
                    'messages' => array(
                        'parameter3.min' => 'Too short',
                        'parameter3.max' => 'Too long'
                    )
                ),
            )
        ), $result);
    }


    public function testCustomMergeCallbackIsApplied()
    {
        $parser = $this->generateParser();
        $parser->setNameMergeCallback(function ($param, $fileName, $filePath) {
            return "foo.$param($fileName)";
        });

        $this->fileSystem->shouldReceive('glob')
            ->once()
            ->with("directory/*")
            ->andReturn(array(
                "directory/file1",
                "directory/file2",
                "directory/file3",
                "directory/file4",
            ));

        $this->innerParser->shouldReceive('accepts')
            ->once()
            ->with("directory/file1")
            ->andReturn(true);
        $this->innerParser->shouldReceive('accepts')
            ->once()
            ->with("directory/file2")
            ->andReturn(true);
        $this->innerParser->shouldReceive('accepts')
            ->once()
            ->with("directory/file3")
            ->andReturn(true);
        $this->innerParser->shouldReceive('accepts')
            ->once()
            ->with("directory/file4")
            ->andReturn(false);

        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("directory/file1")
            ->andReturn($this->generateValidatorData('name1', 'parameter1'));
        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("directory/file2")
            ->andReturn($this->generateValidatorData('name2', 'parameter2'));
        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("directory/file3")
            ->andReturn($this->generateValidatorData('name1', 'parameter3'));

        $result = $parser->parse('directory');
        $this->assertEquals(array(
            'validators' => array(
                'foo.name1(file1)' => array(
                    'rules'    => array(
                        'parameter1' => array(
                            'min:3',
                            'max:6',
                        )
                    ),
                    'messages' => array(
                        'parameter1.min' => 'Too short',
                        'parameter1.max' => 'Too long'
                    )
                ),
                'foo.name2(file2)' => array(
                    'rules'    => array(
                        'parameter2' => array(
                            'min:3',
                            'max:6',
                        )
                    ),
                    'messages' => array(
                        'parameter2.min' => 'Too short',
                        'parameter2.max' => 'Too long'
                    )
                ),
                'foo.name1(file3)' => array(
                    'rules'    => array(
                        'parameter3' => array(
                            'min:3',
                            'max:6',
                        )
                    ),
                    'messages' => array(
                        'parameter3.min' => 'Too short',
                        'parameter3.max' => 'Too long'
                    )
                ),
            )
        ), $result);
    }

    /**
     * @return DirectoryCompositionParser
     */
    protected function generateParser()
    {
        $parser = new DirectoryCompositionParser($this->innerParser, $this->fileSystem);

        return $parser;
    }

    /**
     * @param string $name
     * @param string $parameter
     *
     * @return array
     */
    protected function generateValidatorData($name, $parameter)
    {
        return array(
            'validators' => array(
                $name => array(
                    'rules'    => array(
                        $parameter => array(
                            'min:3',
                            'max:6',
                        )
                    ),
                    'messages' => array(
                        $parameter . '.min' => 'Too short',
                        $parameter . '.max' => 'Too long'
                    )
                )
            )
        );
    }

}