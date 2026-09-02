<?php declare(strict_types=1);

namespace App\Order\Domain\Exception;

use App\Order\Domain\OrderStatus;
use App\Shared\Domain\Exception\RuntimeException;

final class OrderDiscountCannotBeApplied extends RuntimeException
{
	private function __construct(
		string $message = '',
		int $code = 0,
		\Throwable|null $previous = null,
	) {
		parent::__construct($message, $code, $previous);
	}

	public static function becauseOfCurrentStatus(OrderStatus $status): self
	{
		return new self(\sprintf('Discount can only be applied to an order in "draft" status, current status is "%s".', $status->value));
	}
}
