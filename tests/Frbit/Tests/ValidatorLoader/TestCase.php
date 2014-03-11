<?php
/**
 * This class is part of ValidatorRepository
 */

namespace Frbit\Tests\ValidatorLoader;

/**
 * Class TestCase
 * @package Frbit\Tests\ValidatorLoader
 **/
class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    protected $fileSystem;

    public function setUp()
    {
        parent::setUp();
        $this->fileSystem = \Mockery::mock('Illuminate\Filesystem\Filesystem');
    }

    protected function tearDown()
    {
        \Mockery::close();
        parent::tearDown();
    }

}