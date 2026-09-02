<?php declare(strict_types=1);

namespace App\Order\Domain;

use App\Shared\Domain\Id\OrderItemId;
use App\Shared\Domain\Id\ProductId;
use Brick\Math\BigDecimal;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'order_items')]
class OrderItem
{
	public function __construct(
		#[ORM\Id]
		#[ORM\Column(type: 'order_item_id')]
		public readonly OrderItemId $id,
		#[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
		#[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false)]
		public readonly Order $order,
		#[ORM\Column(type: 'product_id')]
		public readonly ProductId $productId,
		#[ORM\Column(type: 'integer')]
		public readonly int $quantity,
		#[ORM\Column(type: 'big_decimal', nullable: true)]
		public readonly BigDecimal|null $unitPrice,
	) {
	}
}
