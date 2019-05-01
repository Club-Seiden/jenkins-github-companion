<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class ReportResultsToGitHubHandler implements RequestHandlerInterface
{
    public static function fromContainer(ContainerInterface $container): self
    {
        return new self();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Use GitHub Status API to report result
    }
}
