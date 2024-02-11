<?php

require 'vendor/autoload.php';

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/../src/')
;

return K10r\Codestyle\PHP81::create($finder);