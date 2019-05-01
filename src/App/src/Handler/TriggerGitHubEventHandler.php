<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\JenkinsManager;
use App\Service\JenkinsManagerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Router\RouterInterface;

final class TriggerGitHubEventHandler implements RequestHandlerInterface
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
     * TriggerGitHubEventHandler constructor.
     * @param JenkinsManagerInterface $jenkinsManager
     * @param RouterInterface $router
     */
    public function __construct(JenkinsManagerInterface $jenkinsManager, RouterInterface $router)
    {
        $this->jenkinsManager = $jenkinsManager;
        $this->router = $router;
    }

    /**
     * @param ContainerInterface $container
     * @return TriggerGitHubEventHandler
     */
    public static function fromContainer(ContainerInterface $container): self
    {
        return new self(
            $container->get(JenkinsManager::class),
            $container->get(RouterInterface::class)
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        var_dump(get_class($this->router));
        die();

        $input = $request->getParsedBody();

        $jenkinsUrl = $this->jenkinsManager->getUrl();
        $ciBuildUser = $this->jenkinsManager->getUser();
        $ciBuildPassword = $this->jenkinsManager->getPassword();

        $job = 'metal-center-ci'; // TODO: get from route param
        $buildEndpoint = $jenkinsUrl . '/job/' . $job . '/build';

        $getCrumbCommand = "wget -q --auth-no-challenge --user $ciBuildUser --password $ciBuildPassword --output-document - \
'10.0.6.21:8080/crumbIssuer/api/xml?xpath=concat(//crumbRequestField,\":\",//crumb)'";

        $crumb = shell_exec($getCrumbCommand);

        $triggerBuildCommand = "cURL -X POST $buildEndpoint --user $ciBuildUser:$ciBuildPassword -H \"$crumb\"";

        $result = shell_exec($triggerBuildCommand);

        return new JsonResponse([]);
    }
}
