<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\TriggerJenkinsBuild;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\Template\TemplateRendererInterface;

final class TriggerGitHubEventHandlerTest extends TestCase
{
    /**
     * @var ContainerInterface|ObjectProphecy
     */
    protected $container;

    /**
     * @var RouterInterface|ObjectProphecy
     */
    protected $router;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);

        $this->container->get(RouterInterface::class)->willReturn($this->router);
        $this->container->has(TemplateRendererInterface::class)->willReturn(false);
    }

    public function testTriggersJenkinsBuildOnPush()
    {
        $eventTriggererHandler = TriggerJenkinsBuild::fromContainer(
            $this->container->reveal(),
            $this->router->reveal(),
            null
        );
        $results = $eventTriggererHandler->handle(
            $this->prophesize(ServerRequestInterface::class)->reveal()
        );
    }
}
