<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Id\Doctrine;

use App\Shared\Domain\Id\OrderItemId;

/**
 * @extends UuidIdType<OrderItemId>
 *
 * phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
 */
final class OrderItemIdType extends UuidIdType
{
	public function getName(): string
	{
		return 'order_item_id';
	}

	protected function getIdClassName(): string
	{
		return OrderItemId::class;
	}
}
