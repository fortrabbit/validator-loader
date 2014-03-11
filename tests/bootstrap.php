<?php
/**
 * This class is part of Develop
 */

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('Frbit\\Tests\\ValidatorLoader\\', __DIR__);
$loader->register();
