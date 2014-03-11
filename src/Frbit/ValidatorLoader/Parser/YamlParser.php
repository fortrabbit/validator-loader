<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\ValidatorLoader\Parser;

use Frbit\ValidatorLoader\Parser;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Parses YAML validator files.
 *
 * Example:
 * ---
 * validators:
 *     name:
 *         rules:
 *             parameter:
 *                 - min:3
 *                 - max:6
 *         messages:
 *             parameter.min: Too short
 *             parameter.max: Too long
 *
 *
 * @package Frbit\ValidatorLoader\Parser
 **/
class YamlParser extends AbstractFileSystemParser
{
    /**
     * @var Yaml
     */
    protected $yaml;

    /**
     * @param Yaml       $yaml
     * @param Filesystem $fileSystem
     */
    public function __construct(Yaml $yaml = null, Filesystem $fileSystem = null)
    {
        parent::__construct($fileSystem);
        $this->yaml = $yaml ? : new Yaml;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($source)
    {
        return $this->yaml->parse($source);
    }

    /**
     * {@inheritdoc}
     */
    public function accepts($source)
    {
        return $this->fileSystem->isFile($source) && preg_match('/\.ya?ml$/', $source) ? true : false;
    }


}