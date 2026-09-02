<?php declare(strict_types=1);

namespace App\Inventory\Domain\Exception;

use App\Shared\Domain\Exception\RuntimeException;
use App\Shared\Domain\Id\ProductId;

final class InventoryIsMissing extends RuntimeException
{
	private function __construct(
		public readonly ProductId $productId,
		string $message = '',
	) {
		parent::__construct($message);
	}

	public static function forProduct(ProductId $productId): self
	{
		return new self($productId, \sprintf('Inventory record is missing for product "%s".', $productId->toString()));
	}
}
