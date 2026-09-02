<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry as DoctrineManagerRegistry;

final readonly class ManagerRegistry
{
	public function __construct(
		private DoctrineManagerRegistry $managerRegistry,
	) {
	}

	public function getManager(string|null $name = null): EntityManagerInterface
	{
		$manager = $this->managerRegistry->getManager($name);

		\assert($manager instanceof EntityManagerInterface);

		return $manager;
	}
}
