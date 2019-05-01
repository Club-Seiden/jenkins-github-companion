<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Container\ContainerInterface;

final class JenkinsManager implements JenkinsManagerInterface
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @param ContainerInterface $container
     * @return JenkinsManager
     */
    public static function fromContainer(ContainerInterface $container): self
    {
        $jenkinsConfig = $container->get('Config');
        $manager = new self();
        $manager->url = $jenkinsConfig['url'];
        $manager->user = $jenkinsConfig['user'];
        $manager->password = $jenkinsConfig['password'];
        return $manager;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}
