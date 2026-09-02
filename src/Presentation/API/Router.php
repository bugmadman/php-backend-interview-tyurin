<?php declare(strict_types=1);

namespace App\Presentation\API;

use Psr\Container\ContainerInterface;
use Slim\App;

interface Router
{
	/**
	 * @param App<ContainerInterface> $app
	 */
	public function setupRoutes(App $app): void;
}
