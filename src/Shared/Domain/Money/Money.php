<?php declare(strict_types=1);

namespace App\Shared\Domain\Money;

use App\Shared\Domain\Currency\CurrencyIsoCode;
use Brick\Math\BigDecimal;

final readonly class Money
{
	public function __construct(
		public BigDecimal $amount,
		public CurrencyIsoCode $currency,
	) {
	}

	public function equals(self $that): bool
	{
		return $this->amount->isEqualTo($that->amount)
			&& $this->currency->equal($that->currency);
	}
}
