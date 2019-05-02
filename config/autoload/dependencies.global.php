<?php

declare(strict_types=1);

use App\Repository\JenkinsJobRepository;
use App\Repository\JenkinsJobRepositoryInterface;
use App\Service\JenkinsManager;

return [
    'dependencies' => [
        'factories'  => [
            JenkinsJobRepositoryInterface::class => [JenkinsJobRepository::class, 'fromContainer'],
            JenkinsManager::class => [JenkinsManager::class, 'fromContainer'],
        ],
    ],
];
