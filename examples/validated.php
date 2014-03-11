<?php
/**
 * This class is part of ValidatorLoader
 */
use Frbit\ValidatorLoader\Parser\ValidatingCombinedParser;
use Frbit\ValidatorLoader\Parser\YamlParser;

require_once __DIR__ . '/../vendor/autoload.php';


$yamlParser      = new YamlParser();
$validatedParser = new ValidatingCombinedParser($yamlParser);

$validatedParser->parse(__DIR__. '/fails/invalid-no-rules.yml');


