<?php declare(strict_types=1);

namespace App\Shared\Domain\Id;

use Ramsey\Uuid\UuidInterface;

/**
 * @implements Id<UuidInterface>
 */
abstract readonly class UuidId implements Id
{
	final protected function __construct(
		private UuidInterface $id,
	) {
	}

	public function equals(Id $other): bool
	{
		return $this::class === $other::class && (string)$this === (string)$other;
	}

	public function unwrap(): UuidInterface
	{
		return $this->id;
	}

	public function jsonSerialize(): string
	{
		return $this->unwrap()->toString();
	}

	public function __toString(): string
	{
		return $this->toString();
	}

	/**
	 * @return non-empty-string
	 */
	public function toString(): string
	{
		return $this->id->toString();
	}

	public function toBytes(): string
	{
		return $this->id->getBytes();
	}

	abstract public static function fromString(string $string): static;

	abstract public static function fromUuid(UuidInterface $uuid): static;
}
