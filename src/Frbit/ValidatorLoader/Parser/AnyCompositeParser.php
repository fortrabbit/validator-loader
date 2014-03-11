<?php
/**
 * This class is part of ValidatorLoader
 */

namespace Frbit\ValidatorLoader\Parser;

use Frbit\ValidatorLoader\Parser;


/**
 * Tries a list of parsers to parse source
 *
 * @package Frbit\ValidatorLoader\Parser
 **/
class AnyCompositeParser implements Parser
{
    /**
     * @var Parser[]
     */
    protected $parsers;

    /**
     * @param Parser[] $parsers
     */
    public function __construct(array $parsers)
    {
        $this->parsers = $parsers;
    }

    /**
     * Takes source and parses content into array
     *
     * @param string $source
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    public function parse($source)
    {
        foreach ($this->parsers as $parser) {
            if ($parser->accepts($source)) {
                return $parser->parse($source);
            }
        }
        throw new \InvalidArgumentException("No parses registered which can handle source");
    }

    /**
     * Checks if source is accepted by parser
     *
     * @param string $source
     *
     * @return bool
     */
    public function accepts($source)
    {
        foreach ($this->parsers as $parser) {
            if ($parser->accepts($source)) {
                return true;
            }
        }

        return false;
    }


}