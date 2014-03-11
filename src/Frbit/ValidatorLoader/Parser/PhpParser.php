<?php
/**
 * This class is part of ValidatorRepository
 */

namespace Frbit\ValidatorLoader\Parser;

use Frbit\ValidatorLoader\Parser;

/**
 * Parses JSON validator files.
 *
 * Example:
 * return array(
 *     "validators" => array(
 *         "name" => array(
 *             "rules" => array(
 *                 "parameter" => array(
 *                     "min:3",
 *                     "max:6"
 *                 )
 *             ),
 *             "messages" => array(
 *                 "parameter.min" => "Too short",
 *                 "parameter.max" => "Too long"
 *             )
 *         )
 *     )
 * )
 *
 * @package Frbit\ValidatorLoader\Parser
 **/
class PhpParser extends AbstractFileSystemParser
{

    /**
     * {@inheritdoc}
     */
    public function parse($source)
    {
        return require $source;
    }

    /**
     * {@inheritdoc}
     */
    public function accepts($source)
    {
        return $this->fileSystem->isFile($source) && preg_match('/\.php$/', $source) ? true : false;
    }


}