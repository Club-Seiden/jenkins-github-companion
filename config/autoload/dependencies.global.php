<?php

declare(strict_types=1);

use App\Service\JenkinsManager;

return [
    'dependencies' => [
        'factories'  => [
            JenkinsManager::class => [JenkinsManager::class, 'fromContainer'],
        ],
    ],
];
