<?php
/**
 * This class is part of ValidatorLoader
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Frbit\ValidatorLoader\Factory;

$loader = Factory::fromDirectory(__DIR__ . "/sources");

$data = array(
    'foo' => 'hallo-is-very-long'
);
$validator = $loader->get("one", $data);

if ($validator->fails()) {
    die(print_r($validator->errors()->toArray(), true));
} else {
    echo "All Good\n";
}