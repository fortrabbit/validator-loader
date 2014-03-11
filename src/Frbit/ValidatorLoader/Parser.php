<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\ValidatorLoader;

/**
 * Interface Parser
 * @package Frbit\ValidatorLoader
 **/
interface Parser
{

    /**
     * Takes source and parses content into array
     *
     * @param string $source
     *
     * @return array
     */
    public function parse($source);

    /**
     * Checks if source is accepted by parser
     *
     * @param string $source
     *
     * @return bool
     */
    public function accepts($source);

}