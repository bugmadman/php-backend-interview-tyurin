<?php declare(strict_types=1);

namespace App\Inventory\Domain\Exception;

use App\Shared\Domain\Exception\RuntimeException;
use App\Shared\Domain\Id\ProductId;

final class ProductIsOutOfStock extends RuntimeException
{
	private function __construct(
		public readonly ProductId $productId,
		public readonly int $missing,
		string $message = '',
	) {
		parent::__construct($message);
	}

	public static function forProduct(ProductId $productId, int $missing): self
	{
		return new self(
			$productId,
			$missing,
			\sprintf('Product "%s" is out of stock, missing %d unit(s).', $productId->toString(), $missing),
		);
	}
}
