<?php declare(strict_types=1);

namespace App\Order\Domain\Exception;

use App\Shared\Domain\Exception\RuntimeException;
use App\Shared\Domain\Id\OrderId;

final class DiscountAlreadyApplied extends RuntimeException
{
	private function __construct(
		public readonly OrderId $orderId,
		string $message = '',
	) {
		parent::__construct($message);
	}

	public static function forOrder(OrderId $orderId): self
	{
		return new self($orderId, \sprintf('Order "%s" already has a discount applied.', $orderId->toString()));
	}
}
