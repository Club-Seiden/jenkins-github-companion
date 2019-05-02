<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Container\ContainerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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
        $jenkinsConfig = $container->get('config')['jenkins'];
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

    /**
     * @return string
     */
    private function getCrumb(): string
    {
        return $this->executeCommand("wget -q --auth-no-challenge --user $this->user --password $this->password --output-document - " .
            "'$this->url/crumbIssuer/api/xml?xpath=concat(//crumbRequestField,\":\",//crumb)'");
    }

    /**
     * @param string $job
     * @return string
     */
    public function triggerBuild(string $job): string
    {
        $buildEndpoint = $this->url . '/job/' . $job . '/build';

        $crumb = $this->getCrumb();

        return $this->executeCommand("cURL -X POST $buildEndpoint --user $this->user:$this->password -H \"$crumb\"");
    }

    /**
     * @param array $command
     * @return string
     */
    private function executeCommand(string $command): string
    {
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(0);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}
