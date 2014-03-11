<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\ValidatorLoader\Parser;
use Frbit\ValidatorLoader\Parser;

/**
 * Dummy parser allowing direct array injection
 * @package Frbit\ValidatorLoader\Parser
 **/
class ArrayParser implements Parser
{
    /**
     * {@inheritdoc}
     */
    public function parse($source)
    {
        return $source;
    }

    /**
     * {@inheritdoc}
     */
    public function accepts($source)
    {
        return is_array($source);
    }

}