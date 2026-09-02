<?php declare(strict_types=1);

namespace App\Presentation\Shared\Http\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CorsMiddleware implements MiddlewareInterface
{
	private const string CORS_HTTP_METHOD = 'OPTIONS';
	private const array ALLOWED_HEADERS = [
		'Origin',
		'Authorization',
		'Content-Type',
		'Access-Control-Allow-Headers',
		'Access-Control-Allow-Credentials',
		'Authorization',
		'Pragma',
		'Expires',
		'Cache-Control',
	];

	public function __construct(
		private readonly ResponseFactoryInterface $responseFactory,
	) {
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		if ($request->getMethod() === self::CORS_HTTP_METHOD) {
			return $this->setupCorsHeaders($request, $this->responseFactory->createResponse());
		}

		return $this->setupCorsHeaders($request, $handler->handle($request));
	}

	private function setupCorsHeaders(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$origin = $request->getHeaderLine('origin');

		if ($origin !== '') {
			$response = $response->withHeader('Access-Control-Allow-Origin', $origin);
		}

		$response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
		$response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
		$response = $response->withHeader('Access-Control-Max-Age', '60');

		return $response->withHeader('Access-Control-Allow-Headers', \implode(', ', self::ALLOWED_HEADERS));
	}
}
