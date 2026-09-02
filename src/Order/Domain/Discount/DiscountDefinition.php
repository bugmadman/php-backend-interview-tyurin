<?php declare(strict_types=1);

namespace App\Order\Domain\Discount;

use App\Order\Domain\OrderDiscountType;
use Brick\Math\BigDecimal;

final readonly class DiscountDefinition
{
	public function __construct(
		public string $code,
		public OrderDiscountType $type,
		public BigDecimal $value,
	) {
	}
}
