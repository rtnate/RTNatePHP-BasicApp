<?php

return [
    'debug' => \DI\env('DEBUG', false),
    'db' => [
        'connection' => \DI\env('DB_CONNECTION', 'mysql'),
        'host' => \DI\env('DB_HOST', '127.0.0.1'),
        'port' => \DI\env('DB_PORT', '3306'),
        'database' => \DI\env('DB_DATABASE', 'mysql'),
        'username' => \DI\env('DB_USERNAME', 'mysql'),
        'password' => \DI\env('DB_PASSWORD', 'mysql'),
        'table_prefix' => \DI\env('DB_TABLE_PREFIX', ''),
        'charset' => \DI\env('DB_CHARSET', 'utf8mb4')
    ],
    'paths' => 
    [
        'root' => DI\env('PATH_ROOT', ""),
        'source' => \DI\env('PATH_SOURCE', '/source/'),
        'providers' => \DI\env('PATH_PROVIDERS', '/source/Providers/*.php'),
        'config' => \DI\env('PATH_CONFIG', '/source/config.php'),
        'routes' => \DI\env('PATH_ROUTES', '/source/Routes/*.php'),
    ],
    'source' => 
    [
        'namespace' => \DI\env('SOURCE_NAMESPACE', "\\") 
    ],
    'site' =>
    [
        'title' => \DI\env('SITE_TITLE', 'site'),
        'url' => \DI\env('SITE_URL', '/'),
        'base' => \DI\env('SITE_BASE', '/'), 
        'asset_path' => \DI\env("SITE_ASSET_PATH", '/assets')
    ],
    'twig' =>
    [
        'template_location' => \DI\Env('TEMPLATE_LOCATION', "/views/"),
        'cache_location' => \DI\Env('TWIG_CACHE_LOCATION', "/cache/"),
        'options' => ['debug' => \DI\env('DEBUG', false)]
    ]
];