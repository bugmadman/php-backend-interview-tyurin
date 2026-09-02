<?php declare(strict_types=1);

namespace App\Order\Infrastructure\Doctrine;

use App\Order\Domain\Order;
use App\Shared\Domain\Id\OrderId;
use App\Shared\Infrastructure\Doctrine\ManagerRegistry;
use Doctrine\DBAL\LockMode;

final readonly class OrderRepository
{
	public function __construct(
		private ManagerRegistry $managerRegistry,
	) {
	}

	public function findForUpdate(OrderId $id): Order|null
	{
		return $this->managerRegistry->getManager()->find(Order::class, $id, LockMode::PESSIMISTIC_WRITE);
	}
}
