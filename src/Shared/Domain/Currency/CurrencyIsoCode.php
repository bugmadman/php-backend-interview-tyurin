<?php declare(strict_types=1);

namespace App\Shared\Domain\Currency;

use Nette\Utils\Strings;

final readonly class CurrencyIsoCode implements \Stringable
{
	private function __construct(
		public string $code,
	) {
	}

	public function equal(CurrencyIsoCode $other): bool
	{
		return $other->code === $this->code;
	}

	public function __toString(): string
	{
		return $this->code;
	}

	public static function fromString(string $code): self
	{
		return new self(Strings::upper($code));
	}

	public static function czk(): self
	{
		return new self('CZK');
	}

	public static function eur(): self
	{
		return new self('EUR');
	}
}
