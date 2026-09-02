<?php declare(strict_types=1);

namespace App\Presentation\API\Order;

use App\Presentation\API\Order\ApplyDiscount\ApplyDiscountHandler;
use App\Presentation\API\Order\ConfirmOrder\ConfirmOrderHandler;
use App\Presentation\API\Order\CreateOrder\CreateOrderHandler;
use App\Presentation\API\Router;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface;

final readonly class OrderRouter implements Router
{
	public function setupRoutes(App $app): void
	{
		$app->group('/api/orders', function (RouteCollectorProxyInterface $proxy) {
			$proxy->post('', CreateOrderHandler::class);
			$proxy->post('/{id}/confirm', ConfirmOrderHandler::class);
			$proxy->post('/{id}/apply-discount', ApplyDiscountHandler::class);
		});
	}
}
