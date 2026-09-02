<?php declare(strict_types=1);

namespace App\Presentation\Shared\Http\Json;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\Route;
use Slim\Routing\RouteContext;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class JsonSerializerMiddleware implements MiddlewareInterface
{
	public function __construct(
		private SerializerInterface $serializer,
	) {
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$contentType = $request->getHeader('content-type')[0] ?? '';

		if (\mb_stripos($contentType, JsonResponseFactory::JSON_CONTENT_TYPE) === false) {
			return $handler->handle($request);
		}

		/** @var Route<ContainerInterface>|null $route */
		$route = $request->getAttribute(RouteContext::ROUTE);
		$requestHandler = $route?->getCallable();

		if ($requestHandler === null ||
			\is_callable($requestHandler) ||
			\is_array($requestHandler) ||
			\is_a($requestHandler, JsonRequestWithParsedBodyHandler::class, true) === false
		) {
			return $handler->handle($request);
		}

		/** @var class-string<JsonRequestWithParsedBodyHandler> $requestHandler */
		$parsedBodyClassName = $requestHandler::getParsedBodyClassName();
		$parsedBody = $this->serializer->deserialize(
			(string)$request->getBody(),
			$parsedBodyClassName,
			JsonEncoder::FORMAT,
		);

		return $handler->handle($request->withParsedBody($parsedBody));
	}
}
