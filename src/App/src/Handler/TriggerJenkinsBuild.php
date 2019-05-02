<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\JenkinsJobRepository;
use App\Repository\JenkinsJobRepositoryInterface;
use App\Service\JenkinsManager;
use App\Service\JenkinsManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Router\RouterInterface;

final class TriggerJenkinsBuild implements RequestHandlerInterface
{
    /**
     * @var JenkinsManager|JenkinsManagerInterface
     */
    private $jenkinsManager;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var JenkinsJobRepositoryInterface
     */
    private $jenkinsJobRepository;

    /**
     * TriggerJenkinsBuild constructor.
     * @param JenkinsManagerInterface $jenkinsManager
     * @param RouterInterface $router
     * @param JenkinsJobRepositoryInterface $jenkinsJobRepository
     */
    public function __construct(
        JenkinsManagerInterface $jenkinsManager,
        RouterInterface $router,
        JenkinsJobRepositoryInterface $jenkinsJobRepository
    ) {
        $this->jenkinsManager = $jenkinsManager;
        $this->router = $router;
        $this->jenkinsJobRepository = $jenkinsJobRepository;
    }

    /**
     * @param ContainerInterface $container
     * @return TriggerJenkinsBuild
     */
    public static function fromContainer(ContainerInterface $container): self
    {
        return new self(
            $container->get(JenkinsManager::class),
            $container->get(RouterInterface::class),
            $container->get(JenkinsJobRepositoryInterface::class)
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $input = json_decode($request->getParsedBody()['payload'], true);

        $jenkinsJob = $this->jenkinsJobRepository->getJobByFullRepositoryName($input['repository']['full_name']);

        $job = $jenkinsJob['job_name'];
        $this->jenkinsManager->triggerBuild($job);

        return new JsonResponse([]);
    }
}
