<?php declare(strict_types=1);

namespace App\Product\Infrastructure\Doctrine;

use App\Product\Domain\Product;
use App\Shared\Domain\Id\ProductId;
use App\Shared\Infrastructure\Doctrine\ManagerRegistry;

final readonly class ProductRepository
{
	public function __construct(
		private ManagerRegistry $managerRegistry,
	) {
	}

	/**
	 * @param list<ProductId> $ids
	 * @return array<string, Product> keyed by product id string
	 */
	public function findByIds(array $ids): array
	{
		if ($ids === []) {
			return [];
		}

		$products = $this->managerRegistry->getManager()
			->createQueryBuilder()
			->select('p')
			->from(Product::class, 'p')
			->where('p.id IN (:ids)')
			->setParameter('ids', $ids)
			->getQuery()
			->getResult();

		$result = [];

		foreach ($products as $product) {
			\assert($product instanceof Product);
			$result[$product->id->toString()] = $product;
		}

		return $result;
	}
}
