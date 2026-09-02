<?php declare(strict_types=1);

namespace App\Presentation\Shared\Http\Json;

use App\Presentation\Shared\Http\StatusCode;
use App\Shared\Domain\Exception\LogicException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class JsonResponseFactory
{
	public const JSON_CONTENT_TYPE = 'application/json';

	public function __construct(
		private ResponseFactoryInterface $responseFactory,
		private SerializerInterface $serializer,
	) {
	}

	public function create(mixed $object, StatusCode $httpStatus = StatusCode::SUCCESS): ResponseInterface
	{
		try {
			$response = $this->responseFactory->createResponse();
			$response = $response->withStatus($httpStatus->value);
			$response->getBody()->write($this->serializer->serialize($object, JsonEncoder::FORMAT));

			return $response->withHeader('Content-Type', self::JSON_CONTENT_TYPE);
		} catch (\RuntimeException $e) {
			throw LogicException::createFromPrevious($e);
		}
	}

	public function createEmpty(StatusCode $httpStatus = StatusCode::NO_CONTENT): ResponseInterface
	{
		return $this->responseFactory->createResponse()->withStatus($httpStatus->value);
	}

	public function createBadRequest(mixed $object): ResponseInterface
	{
		return $this->create($object, StatusCode::BAD_REQUEST);
	}

	public function createMethodNotAllowed(mixed $object): ResponseInterface
	{
		return $this->create($object, StatusCode::NOT_ALLOWED);
	}

	public function createUnauthorized(mixed $object): ResponseInterface
	{
		return $this->create($object, StatusCode::UNAUTHORIZED);
	}

	public function createForbidden(mixed $object): ResponseInterface
	{
		return $this->create($object, StatusCode::FORBIDDEN);
	}

	public function createNotFound(mixed $object): ResponseInterface
	{
		return $this->create($object, StatusCode::NOT_FOUND);
	}

	public function createServerError(mixed $object): ResponseInterface
	{
		return $this->create($object, StatusCode::INTERNAL_SERVER_ERROR);
	}
}
