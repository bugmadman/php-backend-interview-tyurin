<?php declare(strict_types=1);

namespace App\Shared\Domain\Exception;

use Throwable;

final class LogicException extends \LogicException
{
	public static function createForNullValue(string $nameOfVariable): self
	{
		return new self(\sprintf("Variable '%s' must not be null.", $nameOfVariable));
	}

	public static function createFromPrevious(Throwable $e): self
	{
		return new self($e->getMessage(), $e->getCode(), $e);
	}

	public static function createForInvalidType(string $string, mixed $value): self
	{
		return new self(\sprintf('Invalid type for variable "%s". Expected: %s', $string, \gettype($value)));
	}
}
