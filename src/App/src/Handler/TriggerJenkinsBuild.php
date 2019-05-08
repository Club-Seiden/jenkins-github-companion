<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\JenkinsJobRepositoryInterface;
use App\Service\JenkinsManager;
use App\Service\JenkinsManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;
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

        $this->validateRequest($request, $jenkinsJob['webhook_secret']);

        $branch = $input['ref'];

        $job = $jenkinsJob['job_name'];
        $this->jenkinsManager->triggerBuild($job, $branch);

        return new JsonResponse([]);
    }

    /**
     * @param ServerRequest|ServerRequestInterface $request
     * @return bool
     * @throws \Exception
     */
    private function validateRequest(ServerRequestInterface $request, string $hookSecret): bool
    {
        if (empty($request->getHeader('X-Hub-Signature'))) {
            throw new \Exception('Http header \'X-Hub-Signature\' is missing.');
        }

        if (!extension_loaded('hash')) {
            throw new \Exception("Missing 'hash' extension to check the secret code validity.");
        }

        list($algo, $hash) = explode('=', $request->getHeader('X-Hub-Signature')[0], 2) + ['', ''];
        if (!in_array($algo, hash_algos(), TRUE)) {
            throw new \Exception("Hash algorithm '$algo' is not supported.");
        }
        $rawPost = file_get_contents('php://input');
        if ($hash !== hash_hmac($algo, $rawPost, $hookSecret)) {
            throw new \Exception('Hook secret does not match.');
        }

        return true;
    }
}
