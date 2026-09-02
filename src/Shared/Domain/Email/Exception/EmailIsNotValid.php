<?php declare(strict_types=1);

namespace App\Shared\Domain\Email\Exception;

use App\Shared\Domain\Exception\RuntimeException;

final class EmailIsNotValid extends RuntimeException
{
	private function __construct(
		string $message = '',
		int $code = 0,
		\Throwable|null $previous = null,
	) {
		parent::__construct($message, $code, $previous);
	}

	public static function createForEmpty(): self
	{
		return new self('Email must not be empty.');
	}

	public static function create(string $email): self
	{
		return new self(\sprintf('Email "%s" is not valid.', $email));
	}
}
