<?php declare(strict_types=1);

namespace App\Product\Domain;

use App\Shared\Domain\Id\ProductId;
use Brick\Math\BigDecimal;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
class Product
{
	public function __construct(
		#[ORM\Id]
		#[ORM\Column(type: 'product_id')]
		public readonly ProductId $id,
		#[ORM\Column(type: 'string', length: 255)]
		public readonly string $name,
		#[ORM\Column(type: 'string', length: 64)]
		public readonly string $category,
		#[ORM\Column(type: 'big_decimal')]
		public readonly BigDecimal $price,
	) {
	}
}
