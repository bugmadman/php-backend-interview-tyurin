<?php declare(strict_types=1);

namespace App\Presentation\API;

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Exception\HttpNotFoundException;

final readonly class MainRouter
{
	/**
	 * @param list<Router> $routers
	 */
	public function __construct(
		private array $routers = [],
	) {
	}

	/**
	 * @param App<ContainerInterface> $app
	 */
	public function create(App $app): void
	{
		foreach ($this->routers as $router) {
			$router->setupRoutes($app);
		}

		$app->options('/{routes:.+}', static fn ($request, $response) => $response);
		$app->map([
			'GET',
			'POST',
			'PUT',
			'DELETE',
			'PATCH',
		], '/{routes:.+}', function ($request): void {
			throw new HttpNotFoundException($request);
		});
	}
}
