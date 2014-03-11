<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\Tests\ValidatorLoader\Parser;

use Frbit\Tests\ValidatorLoader\TestCase;
use Frbit\ValidatorLoader\Parser\DynamicCompositionParser;

/**
 * @covers  \Frbit\ValidatorLoader\Parser\DynamicCompositionParser
 * @package Frbit\Tests\ValidatorLoader\Parser
 **/
class DynamicCompositionParserTest extends TestCase
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
        new DynamicCompositionParser($this->innerParser);
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

    public function testParserDelegatesToInnerAndKeepsSimpleStructure()
    {

        $parser = $this->generateParser();

        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("source")
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

        $result = $parser->parse('source');
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

    public function testParserDelegatesToInnerAndReplacesVariables()
    {

        $parser = $this->generateParser();

        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("source")
            ->andReturn(array(
                'variables'  => array(
                    'foo'  => '5',
                    'bar'  => 10,
                    'word' => 'Too very'
                ),
                'validators' => array(
                    'name' => array(
                        'rules'    => array(
                            'parameter' => array(
                                'min:<<foo>>',
                                'max:<<bar>>',
                            )
                        ),
                        'messages' => array(
                            'parameter.min' => '<<word>> short',
                            'parameter.max' => '<<word>> long'
                        )
                    )
                )
            ));

        $result = $parser->parse('source');
        $this->assertEquals(array(
            'variables'  => array(
                'foo'  => '5',
                'bar'  => 10,
                'word' => 'Too very'
            ),
            'validators' => array(
                'name' => array(
                    'rules'    => array(
                        'parameter' => array(
                            'min:5',
                            'max:10',
                        )
                    ),
                    'messages' => array(
                        'parameter.min' => 'Too very short',
                        'parameter.max' => 'Too very long'
                    )
                )
            )
        ), $result);
    }

    public function testParserDelegatesToInnerDoesNotInheritForEmptyButCleanupExtendsDirective()
    {

        $parser = $this->generateParser();

        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("source")
            ->andReturn(array(
                'validators' => array(
                    'one' => array(),
                    'two' => array(
                        'extends' => 'one'
                    )
                )
            ));

        $result = $parser->parse('source');
        $this->assertEquals(array(
            'validators' => array(
                'one' => array(),
                'two' => array()
            )
        ), $result);
    }

    public function testParserDelegatesToInnerAndImplementsSimpleInheritance()
    {

        $parser = $this->generateParser();

        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("source")
            ->andReturn(array(
                'validators' => array(
                    'one' => array(
                        'rules'    => array(
                            'parameter' => array(
                                'min:3',
                                'foo',
                            )
                        ),
                        'messages' => array(
                            'parameter.min' => 'Too short',
                            'parameter.foo' => 'Too Foo'
                        )
                    ),
                    'two' => array(
                        'extends' => 'one'
                    )
                )
            ));

        $result = $parser->parse('source');
        $this->assertEquals(array(
            'validators' => array(
                'one' => array(
                    'rules'    => array(
                        'parameter' => array(
                            'min:3',
                            'foo',
                        )
                    ),
                    'messages' => array(
                        'parameter.min' => 'Too short',
                        'parameter.foo' => 'Too Foo'
                    )
                ),
                'two' => array(
                    'rules'    => array(
                        'parameter' => array(
                            'min:3',
                            'foo',
                        )
                    ),
                    'messages' => array(
                        'parameter.min' => 'Too short',
                        'parameter.foo' => 'Too Foo'
                    )
                ),
            )
        ), $result);
    }

    public function testParserDelegatesToInnerAndUsesCustomInheritanceDirective()
    {

        $parser = $this->generateParser();
        $parser->setExtendsDirective('gimme');

        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("source")
            ->andReturn(array(
                'validators' => array(
                    'one' => array(
                        'rules'    => array(
                            'parameter' => array(
                                'min:3',
                                'foo',
                            )
                        ),
                        'messages' => array(
                            'parameter.min' => 'Too short',
                            'parameter.foo' => 'Too Foo'
                        )
                    ),
                    'two' => array(
                        'gimme' => 'one'
                    )
                )
            ));

        $result = $parser->parse('source');
        $this->assertEquals(array(
            'validators' => array(
                'one' => array(
                    'rules'    => array(
                        'parameter' => array(
                            'min:3',
                            'foo',
                        )
                    ),
                    'messages' => array(
                        'parameter.min' => 'Too short',
                        'parameter.foo' => 'Too Foo'
                    )
                ),
                'two' => array(
                    'rules'    => array(
                        'parameter' => array(
                            'min:3',
                            'foo',
                        )
                    ),
                    'messages' => array(
                        'parameter.min' => 'Too short',
                        'parameter.foo' => 'Too Foo'
                    )
                ),
            )
        ), $result);
    }

    public function testParserDelegatesToInnerAndImplementsPartialInheritance()
    {

        $parser = $this->generateParser();

        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("source")
            ->andReturn(array(
                'validators' => array(
                    'one' => array(
                        'rules'    => array(
                            'parameter' => array(
                                'min:3',
                                'max:6',
                                'foo'
                            )
                        ),
                        'messages' => array(
                            'parameter.min' => 'Too short',
                            'parameter.max' => 'Too long',
                            'parameter.foo' => 'Too Foo'
                        )
                    ),
                    'two' => array(
                        'extends'  => 'one',
                        'rules'    => array(
                            'parameter' => array(
                                'min:4',
                            )
                        ),
                        'messages' => array(
                            'parameter.min' => 'Foo short',
                        )
                    )
                )
            ));

        $result = $parser->parse('source');
        $this->assertEquals(array(
            'validators' => array(
                'one' => array(
                    'rules'    => array(
                        'parameter' => array(
                            'min:3',
                            'max:6',
                            'foo'
                        )
                    ),
                    'messages' => array(
                        'parameter.min' => 'Too short',
                        'parameter.max' => 'Too long',
                        'parameter.foo' => 'Too Foo'
                    )
                ),
                'two' => array(
                    'rules'    => array(
                        'parameter' => array(
                            'min:4',
                            'max:6',
                            'foo'
                        )
                    ),
                    'messages' => array(
                        'parameter.min' => 'Foo short',
                        'parameter.max' => 'Too long',
                        'parameter.foo' => 'Too Foo'
                    )
                ),
            )
        ), $result);
    }

    public function testParserDelegatesToInnerAndImplementsOverrideInheritance()
    {

        $parser = $this->generateParser();

        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("source")
            ->andReturn(array(
                'validators' => array(
                    'one' => array(
                        'rules'    => array(
                            'parameter1' => array(
                                'min:3',
                                'max:6',
                            ),
                            'parameter2' => array(
                                'min:3',
                                'max:6',
                            )
                        ),
                        'messages' => array(
                            'parameter1.min' => 'Too short',
                            'parameter1.max' => 'Too long',
                            'parameter2.min' => 'Too short',
                            'parameter2.max' => 'Too long'
                        )
                    ),
                    'two' => array(
                        'extends'  => 'one',
                        'rules'    => array(
                            'parameter1' => array(
                                'min:3',
                                'max:6',
                            ),
                        ),
                        'messages' => array(
                            'parameter1.min' => 'Too short',
                            'parameter1.max' => 'Too long',
                        )
                    )
                )
            ));

        $result = $parser->parse('source');
        $this->assertEquals(array(
            'validators' => array(
                'one' => array(
                    'rules'    => array(
                        'parameter1' => array(
                            'min:3',
                            'max:6',
                        ),
                        'parameter2' => array(
                            'min:3',
                            'max:6',
                        )
                    ),
                    'messages' => array(
                        'parameter1.min' => 'Too short',
                        'parameter1.max' => 'Too long',
                        'parameter2.min' => 'Too short',
                        'parameter2.max' => 'Too long'
                    )
                ),
                'two' => array(
                    'rules'    => array(
                        'parameter1' => array(
                            'min:3',
                            'max:6',
                        ),
                        'parameter2' => array(
                            'min:3',
                            'max:6',
                        )
                    ),
                    'messages' => array(
                        'parameter1.min' => 'Too short',
                        'parameter1.max' => 'Too long',
                        'parameter2.min' => 'Too short',
                        'parameter2.max' => 'Too long'
                    )
                ),
            )
        ), $result);
    }

    /**
     * @expectedException \Frbit\ValidatorLoader\Exception\ImpossibleExtensionException
     * @expectedExceptionMessage Could not extend "one" with "two" because "two" does not exist
     */
    public function testFailWhenTryingToInheritFromNotExistingParent()
    {

        $parser = $this->generateParser();

        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("source")
            ->andReturn(array(
                'validators' => array(
                    'one' => array(
                        'extends' => 'two'
                    )
                )
            ));

        $parser->parse('source');
    }

    /**
     * @expectedException \Frbit\ValidatorLoader\Exception\MissingVariableException
     * @expectedExceptionMessage Could not find variable "foo" for "rules" of "one"
     */
    public function testFailWhenTryingUseNotExistingVariable()
    {

        $parser = $this->generateParser();

        $this->innerParser->shouldReceive('parse')
            ->once()
            ->with("source")
            ->andReturn(array(
                'validators' => array(
                    'one' => array(
                        'rules' => array(
                            'parameter' => array(
                                'min:<<foo>>'
                            )
                        )
                    )
                )
            ));

        $parser->parse('source');
    }

    /**
     * @return DynamicCompositionParser
     */
    protected function generateParser()
    {
        $parser = new DynamicCompositionParser($this->innerParser, $this->fileSystem);

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