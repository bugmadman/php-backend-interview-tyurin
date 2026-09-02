<?php declare(strict_types=1);

namespace App\Inventory\Domain;

use App\Shared\Domain\Id\ProductId;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'inventory')]
class Inventory
{
	public function __construct(
		#[ORM\Id]
		#[ORM\Column(type: 'product_id')]
		public readonly ProductId $productId,
		#[ORM\Column(type: 'integer')]
		private(set) int $available,
	) {
	}

	public function decrease(int $quantity): void
	{
		$this->available -= $quantity;
	}
}
