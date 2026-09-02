<?php declare(strict_types=1);

namespace App\Inventory\Infrastructure\Doctrine;

use App\Inventory\Domain\Inventory;
use App\Shared\Domain\Id\ProductId;
use App\Shared\Infrastructure\Doctrine\ManagerRegistry;
use Doctrine\DBAL\LockMode;

final readonly class InventoryRepository
{
	public function __construct(
		private ManagerRegistry $managerRegistry,
	) {
	}

	public function findForUpdate(ProductId $productId): Inventory|null
	{
		return $this->managerRegistry->getManager()->find(Inventory::class, $productId, LockMode::PESSIMISTIC_WRITE);
	}
}
