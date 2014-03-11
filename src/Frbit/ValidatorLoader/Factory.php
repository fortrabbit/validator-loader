<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\ValidatorLoader;

use Frbit\ValidatorLoader\Parser\AnyCompositeParser;
use Frbit\ValidatorLoader\Parser\ArrayParser;
use Frbit\ValidatorLoader\Parser\DirectoryCompositionParser;
use Frbit\ValidatorLoader\Parser\DynamicCompositionParser;
use Frbit\ValidatorLoader\Parser\JsonParser;
use Frbit\ValidatorLoader\Parser\PhpParser;
use Frbit\ValidatorLoader\Parser\ValidatingCombinedParser;
use Frbit\ValidatorLoader\Parser\YamlParser;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Validation\Factory as ValidatorFactory;

/**
 * Factor for validator loaders.
 *
 * @package Frbit\ValidatorLoader
 **/
class Factory
{
    /**
     * Create new loader from file
     *
     * @param string           $file
     * @param ValidatorFactory $validatorFactory
     * @param Filesystem       $fileSystem
     *
     * @return Loader
     * @throws \InvalidArgumentException
     */
    public static function fromFile($file, ValidatorFactory $validatorFactory = null, Filesystem $fileSystem = null)
    {
        $fileSystem = $fileSystem ? : new Filesystem();
        if (!$fileSystem->isFile($file)) {
            throw new \InvalidArgumentException("Source file \"$file\" seems does not exist or is not accessible");
        }
        $parser = static::getFileParser($fileSystem);

        return static::factory($file, $parser, $validatorFactory);
    }

    /**
     * Create new loader from directory
     *
     * @param string           $directory
     * @param bool             $prefixed
     * @param ValidatorFactory $validatorFactory
     * @param Filesystem       $fileSystem
     *
     * @throws \InvalidArgumentException
     * @return Loader
     */
    public static function fromDirectory($directory, $prefixed = false, ValidatorFactory $validatorFactory = null, Filesystem $fileSystem = null)
    {
        $fileSystem = $fileSystem ? : new Filesystem();
        if (!$fileSystem->isDirectory($directory)) {
            throw new \InvalidArgumentException("Source directory \"$directory\" seems does not exist or is not accessible");
        }

        $parser = new DirectoryCompositionParser(static::getFileParser($fileSystem), $fileSystem);
        if (!$prefixed) {
            $parser->setNameMergeCallback(function ($name) {
                return $name;
            });
        }

        return static::factory($directory, $parser, $validatorFactory);
    }

    /**
     * Create new loader from array
     *
     * @param array            $validators
     * @param ValidatorFactory $validatorFactory
     *
     * @throws \InvalidArgumentException
     * @return Loader
     */
    public static function fromArray(array $validators, ValidatorFactory $validatorFactory = null)
    {
        $parser = new ArrayParser();

        return static::factory($validators, $parser, $validatorFactory);
    }

    /**
     * Create new loader from custom parser with given source
     *
     * @param mixed            $source
     * @param Parser           $parser
     * @param ValidatorFactory $validatorFactory
     *
     * @return Loader
     */
    public static function fromCustom($source, Parser $parser, ValidatorFactory $validatorFactory = null)
    {
        return static::factory($source, $parser, $validatorFactory);
    }

    /**
     * Create new loader from file
     *
     * @param mixed            $source
     * @param Parser           $parser
     * @param ValidatorFactory $validatorFactory
     *
     * @throws \InvalidArgumentException
     * @return Loader
     */
    protected static function factory($source, Parser $parser, ValidatorFactory $validatorFactory = null)
    {
        $parser = new DynamicCompositionParser($parser);
        $parser = new ValidatingCombinedParser($parser);

        $validators = $parser->parse($source);

        return new Loader($validators, $validatorFactory);
    }

    /**
     * @param Filesystem $fileSystem
     *
     * @return AnyCompositeParser
     */
    protected static function getFileParser(Filesystem $fileSystem = null)
    {
        $fileParsers = array(new PhpParser($fileSystem), new JsonParser($fileSystem), new YamlParser(null, $fileSystem));

        return new AnyCompositeParser($fileParsers);
    }

}