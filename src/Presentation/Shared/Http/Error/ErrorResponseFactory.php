<?php declare(strict_types=1);

namespace App\Presentation\Shared\Http\Error;

use App\Presentation\Shared\Http\Json\JsonResponseFactory;
use App\Presentation\Shared\Http\StatusCode;
use Psr\Http\Message\ResponseInterface;

final readonly class ErrorResponseFactory
{
	public function __construct(
		private JsonResponseFactory $jsonResponseFactory,
	) {
	}

	/**
	 * @param array<string, mixed> $errors
	 */
	public function createErrorResponse(
		StatusCode $httpStatus,
		string $code,
		array $errors,
	): ResponseInterface {
		return $this->jsonResponseFactory->create(new ErrorResponse($code, $errors), $httpStatus);
	}
}
