<?php declare(strict_types=1);

namespace App\Presentation\API\Order\ApplyDiscount;

use App\Order\Domain\Discount\PricingPreview;
use App\Order\Domain\Order;
use App\Shared\Domain\Exception\LogicException;

final readonly class ApplyDiscountResponse
{
	public function __construct(
		public string $orderId,
		public string $status,
		public DiscountResponse $discount,
		public PricingPreviewResponse $pricingPreview,
	) {
	}

	public static function fromOrder(Order $order, PricingPreview $pricingPreview): self
	{
		if ($order->discountCode === null || $order->discountType === null || $order->discountValue === null) {
			throw LogicException::createForNullValue('order->discountCode');
		}

		return new self(
			orderId: $order->id->toString(),
			status: $order->status->value,
			discount: new DiscountResponse(
				code: $order->discountCode,
				type: $order->discountType->value,
				value: (string)$order->discountValue,
			),
			pricingPreview: new PricingPreviewResponse(
				subtotal: (string)$pricingPreview->subtotal,
				discountTotal: (string)$pricingPreview->discountTotal,
				total: (string)$pricingPreview->total,
			),
		);
	}
}
