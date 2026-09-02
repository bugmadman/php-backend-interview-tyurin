<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Id\Doctrine;

use App\Shared\Domain\Id\OrderId;

/**
 * @extends UuidIdType<OrderId>
 *
 * phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
 */
final class OrderIdType extends UuidIdType
{
	public function getName(): string
	{
		return 'order_id';
	}

	protected function getIdClassName(): string
	{
		return OrderId::class;
	}
}
