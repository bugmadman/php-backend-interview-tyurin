<?php declare(strict_types=1);

namespace App\Presentation\API\Order\Response;

use App\Order\Domain\OrderItem;

final readonly class OrderItemResponse
{
	public function __construct(
		public string $id,
		public string $productId,
		public int $quantity,
		public string|null $unitPrice,
	) {
	}

	public static function fromEntity(OrderItem $item): self
	{
		return new self(
			id: $item->id->toString(),
			productId: $item->productId->toString(),
			quantity: $item->quantity,
			unitPrice: $item->unitPrice === null ? null : (string)$item->unitPrice,
		);
	}
}
