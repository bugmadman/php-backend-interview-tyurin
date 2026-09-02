<?php declare(strict_types=1);

namespace App\Presentation\API\Hello;

use App\Presentation\API\Hello\GetHello\GetHelloHandler;
use App\Presentation\API\Router;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface;

final readonly class HelloRouter implements Router
{
	public function setupRoutes(App $app): void
	{
		$app->group('/hello', function (RouteCollectorProxyInterface $proxy) {
			$proxy->get('', GetHelloHandler::class);
		});
	}
}
