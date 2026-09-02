<?php declare(strict_types=1);

namespace App\Presentation\API\Order\ApplyDiscount;

final readonly class DiscountResponse
{
	public function __construct(
		public string $code,
		public string $type,
		public string $value,
	) {
	}
}
