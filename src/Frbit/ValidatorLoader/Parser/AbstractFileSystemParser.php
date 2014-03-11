<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\ValidatorLoader\Parser;

use Frbit\ValidatorLoader\Parser;
use Illuminate\Filesystem\Filesystem;

/**
 * Base class for parsers relying on the file system
 *
 * @package Frbit\ValidatorLoader\Parser
 * @codeCoverageIgnore Just a constructor..
 **/
abstract class AbstractFileSystemParser implements Parser
{
    /**
     * @var Filesystem
     */
    protected $fileSystem;

    public function __construct(Filesystem $fileSystem = null)
    {
        $this->fileSystem = $fileSystem ? : new Filesystem;
    }


}