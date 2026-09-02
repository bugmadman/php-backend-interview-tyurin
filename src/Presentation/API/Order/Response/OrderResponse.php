<?php declare(strict_types=1);

namespace App\Presentation\API\Order\Response;

use App\Order\Domain\Order;

final readonly class OrderResponse
{
	/**
	 * @param list<OrderItemResponse> $items
	 */
	public function __construct(
		public string $id,
		public string $customerId,
		public string $status,
		public string|null $totalAmount,
		public string $createdAt,
		public array $items,
	) {
	}

	public static function fromEntity(Order $order): self
	{
		return new self(
			id: $order->id->toString(),
			customerId: $order->customerId->toString(),
			status: $order->status->value,
			totalAmount: $order->totalAmount === null ? null : (string)$order->totalAmount,
			createdAt: $order->createdAt->format(\DateTimeInterface::ATOM),
			items: \array_values(\array_map(
				OrderItemResponse::fromEntity(...),
				$order->getItems()->toArray(),
			)),
		);
	}
}
