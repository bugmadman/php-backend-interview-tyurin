<?php declare(strict_types=1);

namespace App\Customer\Infrastructure\Doctrine;

use App\Customer\Domain\Customer;
use App\Shared\Domain\Id\CustomerId;
use App\Shared\Infrastructure\Doctrine\ManagerRegistry;

final readonly class CustomerRepository
{
	public function __construct(
		private ManagerRegistry $managerRegistry,
	) {
	}

	public function find(CustomerId $id): Customer|null
	{
		return $this->managerRegistry->getManager()->find(Customer::class, $id);
	}
}
