<?php declare(strict_types=1);

namespace App\Presentation\API\Order\ApplyDiscount;

final readonly class PricingPreviewResponse
{
	public function __construct(
		public string $subtotal,
		public string $discountTotal,
		public string $total,
	) {
	}
}
