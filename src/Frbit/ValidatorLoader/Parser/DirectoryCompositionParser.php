<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\ValidatorLoader\Parser;

use Frbit\ValidatorLoader\Parser;
use Illuminate\Filesystem\Filesystem;


/**
 * Class DirectoryParser
 * @package Frbit\ValidatorLoader\Parser
 **/
class DirectoryCompositionParser implements Parser
{
    /**
     * @var Parser
     */
    protected $fileParser;

    /**
     * @var callable
     */
    protected $nameMergeCallback;

    /**
     * @param Parser     $fileParser
     * @param Filesystem $fileSystem
     */
    public function __construct(Parser $fileParser, Filesystem $fileSystem = null)
    {
        $this->fileParser        = $fileParser;
        $this->fileSystem        = $fileSystem ? : new Filesystem;
        $this->nameMergeCallback = function ($parameterName, $fileName, $filePath) {
            return "$fileName.$parameterName";
        };
    }

    /**
     * Sets a callable which is used to merge parameters from a file.
     * Per default the parameters will be renamed to "<file-name>.<parameter-name>", where
     * <file-name> is the name of the file without suffix and directories:
     *
     * function ($parameterName, $fileName, $filePath) {
     *     return "$fileName.$parameterName";
     * }
     *
     * @param callable $joinCallback
     */
    public function setNameMergeCallback($joinCallback)
    {
        $this->nameMergeCallback = $joinCallback;
    }

    /**
     * Takes directory, scans for files and parses them
     *
     * @param string $source
     *
     * @return array
     */
    public function parse($source)
    {
        $merged = array();
        foreach ($this->fileSystem->glob("$source/*") as $file) {
            if ($this->fileParser->accepts($file)) {
                $name   = preg_replace('#^.+/#', '', $file);
                $name   = preg_replace('#\.[^.]+$#', '', $name);
                $parsed = $this->fileParser->parse($file);
                static::extend($merged, $parsed, $name, $file, $this->nameMergeCallback);
            }
        }

        return $merged;
    }

    /**
     * Checks if source is a directory
     *
     * @param string $source
     *
     * @return bool
     */
    public function accepts($source)
    {
        return $this->fileSystem->isDirectory($source);
    }

    /**
     * @param array    $merged
     * @param array    $data
     * @param string   $prefix
     * @param string   $file
     * @param callable $mergeCallback
     */
    protected static function extend(array &$merged, array $data, $prefix, $file, $mergeCallback)
    {
        foreach ($data as $key => $values) {
            $updated = array();
            foreach ($values as $name => $definition) {
                $newName           = call_user_func($mergeCallback, $name, $prefix, $file);
                $updated[$newName] = $definition;
            }
            if (!isset($merged[$key])) {
                $merged[$key] = $updated;
            } else {
                $merged[$key] = array_merge($merged[$key], $updated);
            }
        }
    }


}