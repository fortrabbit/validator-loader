<?php
/**
 * This class is part of ValidatorRepository
 */

namespace Frbit\ValidatorLoader\Parser;

use Frbit\ValidatorLoader\Parser;
use Illuminate\Filesystem\Filesystem;


/**
 * Parses JSON validator files.
 *
 * Example:
 * {
 *     "validators": {
 *         "name": {
 *             "rules": {
 *                 "parameter": [
 *                     "min:3",
 *                     "max:6"
 *                 ]
 *             },
 *             "messages": {
 *                 "parameter.min": "Too short",
 *                 "parameter.max": "Too long"
 *             }
 *         }
 *     }
 * }
 *
 * @package Frbit\ValidatorLoader\Parser
 **/
class JsonParser extends AbstractFileSystemParser
{

    /**
     * {@inheritdoc}
     */
    public function parse($source)
    {
        $json   = $this->fileSystem->get($source);
        $result = json_decode($json, true);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function accepts($source)
    {
        return $this->fileSystem->isFile($source) && preg_match('/\.(?:js|json)$/', $source) ? true : false;
    }


}