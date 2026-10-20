<?php

declare(strict_types=1);

// The plugin is tested inside a Shopware installation, e.g. custom/plugins/TinectRedirects.
$projectRoot = $_SERVER['PROJECT_ROOT'] ?? \dirname(__DIR__, 4);

require $projectRoot . '/vendor/autoload.php';

// Prefer the sources of this checkout over an installed copy, which might be part of an optimized classmap
\spl_autoload_register(static function (string $class): void {
    $namespaces = [
        'Tinect\\Redirects\\Tests\\' => __DIR__ . '/',
        'Tinect\\Redirects\\' => \dirname(__DIR__) . '/src/',
    ];

    foreach ($namespaces as $prefix => $directory) {
        if (!\str_starts_with($class, $prefix)) {
            continue;
        }

        $file = $directory . \str_replace('\\', '/', \substr($class, \strlen($prefix))) . '.php';

        if (\is_file($file)) {
            require $file;
        }

        return;
    }
}, true, true);
