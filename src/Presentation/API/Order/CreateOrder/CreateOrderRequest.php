<?php declare(strict_types=1);

namespace App\Presentation\API\Order\CreateOrder;

final class CreateOrderRequest
{
	/**
	 * @param list<mixed> $items
	 */
	public function __construct(
		public readonly mixed $customerId = null,
		public readonly array $items = [],
	) {
	}
}
