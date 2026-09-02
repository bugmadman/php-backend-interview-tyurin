<?php declare(strict_types=1);

namespace App\Order\Domain\Discount;

use Brick\Math\BigDecimal;

final readonly class PricingPreview
{
	public function __construct(
		public BigDecimal $subtotal,
		public BigDecimal $discountTotal,
		public BigDecimal $total,
	) {
	}
}
