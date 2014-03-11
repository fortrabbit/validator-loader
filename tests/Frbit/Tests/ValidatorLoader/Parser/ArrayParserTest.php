<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\Tests\ValidatorLoader\Parser;

use Frbit\Tests\ValidatorLoader\TestCase;
use Frbit\ValidatorLoader\Parser\ArrayParser;


/**
 * @covers  \Frbit\ValidatorLoader\Parser\ArrayParser
 * @package Frbit\Tests\ValidatorLoader\Parser
 **/
class ArrayParserTest extends TestCase
{

    public function testCreateInstance()
    {
        new ArrayParser();
        $this->assertTrue(true);
    }

    public function testAcceptsArray()
    {
        $parser = $this->generateParser();
        $this->assertTrue($parser->accepts(array()));
    }

    public function testDoesNotAcceptScalar()
    {
        $parser = $this->generateParser();
        $this->assertFalse($parser->accepts('scalar'));
    }


    public function testJustHandsOverInputArray()
    {
        $parser = $this->generateParser();

        $result = $parser->parse(array('foo' => 'bar'));
        $this->assertEquals(array('foo' => 'bar'), $result);
    }

    /**
     * @return ArrayParser
     */
    protected function generateParser()
    {
        $parser = new ArrayParser();

        return $parser;
    }

}