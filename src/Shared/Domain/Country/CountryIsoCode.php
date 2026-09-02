<?php declare(strict_types=1);

namespace App\Shared\Domain\Country;

use Nette\Utils\Strings;

final readonly class CountryIsoCode
{
	private function __construct(
		public string $code,
	) {
	}

	public function equal(CountryIsoCode $other): bool
	{
		return $other->code === $this->code;
	}

	public function toString(): string
	{
		return $this->code;
	}

	public function __toString(): string
	{
		return $this->toString();
	}

	public function toUpper(): string
	{
		return Strings::upper($this->code);
	}

	public static function fromString(string $code): self
	{
		return new self(Strings::lower($code));
	}
}
