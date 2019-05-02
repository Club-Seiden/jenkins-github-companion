<?php

declare(strict_types=1);

namespace App\Repository;

use Psr\Container\ContainerInterface;

final class JenkinsJobRepository implements JenkinsJobRepositoryInterface
{
    const JENKINS_JOBS_CONFIG_KEY = 'jenkins_jobs';
    const REPOSITORY_FULL_NAME_CONFIG_KEY = 'repository_full_name';
    const WEBHOOK_SECRET_CONFIG_KEY = 'webhook_secret';

    /**
     * @var array
     */
    private $config;

    /**
     * JenkinsJobsRepository constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param ContainerInterface $container
     * @return JenkinsJobRepository
     */
    public static function fromContainer(ContainerInterface $container): self
    {
        return new self(
            $container->get('config')[self::JENKINS_JOBS_CONFIG_KEY]
        );
    }

    /**
     * @param string $name
     * @return array
     */
    public function getJobByFullRepositoryName(string $name): array
    {
        foreach ($this->config as $job) {
            if ($job[self::REPOSITORY_FULL_NAME_CONFIG_KEY] === $name) {
                return $job;
            }
        }
        return [];
    }
}
