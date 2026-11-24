<?php
declare(strict_types=1);

require_once dirname(__DIR__, 4) . '/kirby/bootstrap.php';
require_once dirname(__DIR__) . '/index.php';

new Kirby([
    'roots' => [
        'plugins' => __DIR__,
    ],
    'request' => [
        'body' => [
            'blocks' => [
                ['type' => 'text', 'content' => ['anchor' => 'section']],
                ['type' => 'text', 'content' => ['anchor' => 'section']],
            ],
        ],
    ],
]);