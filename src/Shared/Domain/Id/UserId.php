<?php declare(strict_types=1);

namespace App\Shared\Domain\Id;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class UserId extends UuidId
{
	public static function generate(): self
	{
		return self::fromUuid(Uuid::uuid7());
	}

	public static function fromString(string $string): static
	{
		return new self(Uuid::fromString($string));
	}

	public static function fromUuid(UuidInterface $uuid): static
	{
		return new self($uuid);
	}
}
