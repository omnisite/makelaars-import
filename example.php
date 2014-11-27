<?php

require __DIR__ . '/vendor/autoload.php';

$import = new MakelaarsImport\Import('demo');
$import->run();

var_dump($import->getUpdated([]));