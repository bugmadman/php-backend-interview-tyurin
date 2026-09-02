<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Id\Doctrine;

use App\Shared\Domain\Id\ProductId;

/**
 * @extends UuidIdType<ProductId>
 *
 * phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
 */
final class ProductIdType extends UuidIdType
{
	public function getName(): string
	{
		return 'product_id';
	}

	protected function getIdClassName(): string
	{
		return ProductId::class;
	}
}
