<?php declare(strict_types=1);

namespace Tests\Unit\Shared\Domain\Money;

use App\Shared\Domain\Currency\CurrencyIsoCode;
use App\Shared\Domain\Money\Money;
use Brick\Math\BigDecimal;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
	public function testEqualsIsTrueForSameAmountAndCurrency(): void
	{
		$a = new Money(BigDecimal::of('10.00'), CurrencyIsoCode::eur());
		$b = new Money(BigDecimal::of('10.00'), CurrencyIsoCode::eur());

		self::assertTrue($a->equals($b));
	}

	public function testEqualsIsFalseForDifferentAmount(): void
	{
		$a = new Money(BigDecimal::of('10.00'), CurrencyIsoCode::eur());
		$b = new Money(BigDecimal::of('10.01'), CurrencyIsoCode::eur());

		self::assertFalse($a->equals($b));
	}

	public function testEqualsIsFalseForDifferentCurrency(): void
	{
		$a = new Money(BigDecimal::of('10.00'), CurrencyIsoCode::eur());
		$b = new Money(BigDecimal::of('10.00'), CurrencyIsoCode::czk());

		self::assertFalse($a->equals($b));
	}

	public function testEqualsIgnoresScaleDifferences(): void
	{
		$a = new Money(BigDecimal::of('10'), CurrencyIsoCode::eur());
		$b = new Money(BigDecimal::of('10.00'), CurrencyIsoCode::eur());

		self::assertTrue($a->equals($b));
	}
}
