<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\Tests\ValidatorLoader\Parser;

use Frbit\Tests\ValidatorLoader\TestCase;
use Frbit\ValidatorLoader\Parser\AnyCompositeParser;

/**
 * @covers  \Frbit\ValidatorLoader\Parser\AnyCompositeParser
 * @package Frbit\Tests\ValidatorLoader\Parser
 **/
class AnyCompositeParserTest extends TestCase
{

    /**
     * @var \Mockery\MockInterface
     */
    protected $innerParser1;

    /**
     * @var \Mockery\MockInterface
     */
    protected $innerParser2;

    public function setUp()
    {
        parent::setUp();
        $this->innerParser1 = \Mockery::mock('Frbit\ValidatorLoader\Parser');
        $this->innerParser2 = \Mockery::mock('Frbit\ValidatorLoader\Parser');
    }

    public function testCreateInstance()
    {
        new AnyCompositeParser(array());
        $this->assertTrue(true);
    }

    public function testReturnsTrueIfOneInnerParserAcceptsAndFalseOtherwise()
    {
        $parser = $this->generateParser();
        $this->innerParser1->shouldReceive('accepts')
            ->times(3)
            ->with('source')
            ->andReturn(false, true, false);
        $this->innerParser2->shouldReceive('accepts')
            ->twice()
            ->with('source')
            ->andReturn(true, false);
        $this->assertTrue($parser->accepts('source'));
        $this->assertTrue($parser->accepts('source'));
        $this->assertFalse($parser->accepts('source'));
    }

    public function testHandsOverInputToFirstAcceptingParserAndReturnsItsResult()
    {
        $parser = $this->generateParser();

        $this->innerParser1->shouldReceive('accepts')
            ->twice()
            ->with('source')
            ->andReturn(false, true);
        $this->innerParser2->shouldReceive('accepts')
            ->once()
            ->with('source')
            ->andReturn(true);

        $this->innerParser1->shouldReceive('parse')
            ->once()
            ->with('source')
            ->andReturn('parsed1');
        $this->innerParser2->shouldReceive('parse')
            ->once()
            ->with('source')
            ->andReturn('parsed2');

        $this->assertSame('parsed2', $parser->parse('source'));
        $this->assertSame('parsed1', $parser->parse('source'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No parses registered which can handle source
     */
    public function testFailsWithExceptionIfNoParserCanParse()
    {
        $parser = $this->generateParser();

        $this->innerParser1->shouldReceive('accepts')
            ->once()
            ->with('source')
            ->andReturn(false);
        $this->innerParser2->shouldReceive('accepts')
            ->once()
            ->with('source')
            ->andReturn(false);

        $parser->parse('source');
    }

    /**
     * @return AnyCompositeParser
     */
    protected function generateParser()
    {
        $parser = new AnyCompositeParser(array($this->innerParser1, $this->innerParser2));

        return $parser;
    }

}