<?php declare(strict_types=1);

namespace App\Shared\Domain\Id;

/**
 * @template T of string|int
 * @implements Id<T>
 */
abstract readonly class ScalarId implements Id
{
	/**
	 * @param T $value
	 */
	final protected function __construct(
		public string|int $value,
	) {
	}

	public function equals(Id $other): bool
	{
		return $this::class === $other::class && (string)$this === (string)$other;
	}

	/**
	 * @return T
	 */
	public function unwrap(): mixed
	{
		return $this->value;
	}

	public function __toString(): string
	{
		return (string)$this->unwrap();
	}

	public function jsonSerialize(): mixed
	{
		return $this->unwrap();
	}

	/**
	 * @template P of string|int
	 * @param P $scalar
	 * @return static
	 */
	abstract public static function from(int|string $scalar): static;
}
