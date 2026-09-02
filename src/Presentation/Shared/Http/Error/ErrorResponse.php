<?php declare(strict_types=1);

namespace App\Presentation\Shared\Http\Error;

final readonly class ErrorResponse
{
	/**
	 * @param array<string, mixed> $error
	 */
	public function __construct(
		public string $code,
		public array $error,
	) {
	}
}
