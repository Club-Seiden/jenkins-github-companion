<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\JenkinsJobRepositoryInterface;
use App\Service\GithubWebhookRequestValidator;
use App\Service\JenkinsManager;
use App\Service\JenkinsManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;
use Zend\Expressive\Router\RouterInterface;
use Zend\Validator\ValidatorInterface;
use Zend\Validator\ValidatorPluginManager;

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
     * @var ValidatorInterface
     */
    private $githubWebhookRequestValidator;

    /**
     * TriggerJenkinsBuild constructor.
     * @param JenkinsManagerInterface $jenkinsManager
     * @param RouterInterface $router
     * @param JenkinsJobRepositoryInterface $jenkinsJobRepository
     * @param ValidatorInterface $githubWebhookRequestValidator
     */
    public function __construct(
        JenkinsManagerInterface $jenkinsManager,
        RouterInterface $router,
        JenkinsJobRepositoryInterface $jenkinsJobRepository,
        ValidatorInterface $githubWebhookRequestValidator
    ) {
        $this->jenkinsManager = $jenkinsManager;
        $this->router = $router;
        $this->jenkinsJobRepository = $jenkinsJobRepository;
        $this->githubWebhookRequestValidator = $githubWebhookRequestValidator;
    }

    /**
     * @param ContainerInterface $container
     * @return TriggerJenkinsBuild
     */
    public static function fromContainer(ContainerInterface $container): self
    {
        $validatorPluginManager = $container->get(ValidatorPluginManager::class);
        $gitHubWebHookRequestValidator = $validatorPluginManager->get(GithubWebhookRequestValidator::class);
        return new self(
            $container->get(JenkinsManager::class),
            $container->get(RouterInterface::class),
            $container->get(JenkinsJobRepositoryInterface::class),
            $container->get(ValidatorPluginManager::class)->get(GithubWebhookRequestValidator::class)
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

        if (!$this->githubWebhookRequestValidator->isValid($request)) {
            throw new \Exception($this->githubWebhookRequestValidator->getMessages()[0]);
        }

        $branch = $input['ref'];

        $job = $jenkinsJob['job_name'];
        $this->jenkinsManager->triggerBuild($job, $branch);

        return new JsonResponse([]);
    }
}
