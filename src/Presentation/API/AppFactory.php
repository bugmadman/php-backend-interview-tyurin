<?php declare(strict_types=1);

namespace App\Presentation\API;

use App\Presentation\Shared\Http\Error\ErrorMiddleware;
use App\Presentation\Shared\Http\Json\JsonSerializerMiddleware;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory as SlimAppFactory;

final readonly class AppFactory
{
	public function __construct(
		private ContainerInterface $container,
		private JsonSerializerMiddleware $jsonSerializerMiddleware,
		private MainRouter $router,
		private ErrorMiddleware $errorMiddleware,
	) {
	}

	/**
	 * @return App<ContainerInterface>
	 */
	public function create(): App
	{
		$app = SlimAppFactory::createFromContainer($this->container);
		$this->router->create($app);
		$app->addMiddleware($this->jsonSerializerMiddleware);
		$app->addRoutingMiddleware();
		$app->addMiddleware($this->errorMiddleware);

		return $app;
	}
}
