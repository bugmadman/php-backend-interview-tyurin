<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Id\Doctrine;

use App\Shared\Domain\Id\CustomerId;

/**
 * @extends UuidIdType<CustomerId>
 *
 * phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
 */
final class CustomerIdType extends UuidIdType
{
	public function getName(): string
	{
		return 'customer_id';
	}

	protected function getIdClassName(): string
	{
		return CustomerId::class;
	}
}
