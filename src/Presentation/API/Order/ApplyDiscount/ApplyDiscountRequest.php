<?php declare(strict_types=1);

namespace App\Presentation\API\Order\ApplyDiscount;

final class ApplyDiscountRequest
{
	public function __construct(
		public readonly mixed $code = null,
	) {
	}
}
