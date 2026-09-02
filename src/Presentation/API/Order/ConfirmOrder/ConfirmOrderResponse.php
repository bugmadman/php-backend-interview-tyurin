<?php declare(strict_types=1);

namespace App\Presentation\API\Order\ConfirmOrder;

use App\Order\Domain\Order;

final readonly class ConfirmOrderResponse
{
	public function __construct(
		public string $orderId,
		public string $status,
		public string|null $totalAmount,
	) {
	}

	public static function fromEntity(Order $order): self
	{
		return new self(
			orderId: $order->id->toString(),
			status: $order->status->value,
			totalAmount: $order->totalAmount === null ? null : (string)$order->totalAmount,
		);
	}
}
