<?php

use Doctum\Doctum;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->exclude('vendor')
    ->exclude('Tests')
    ->in(__DIR__ . '/includes');

return new Doctum($iterator, [
    'title' => 'JustB2B Plugin API',
    'build_dir' => __DIR__ . '/docs/api/build',
    'cache_dir' => __DIR__ . '/docs/api/cache',
    'source_dir' => __DIR__,
]);


// php guide-generator.php