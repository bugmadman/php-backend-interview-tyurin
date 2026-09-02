<?php declare(strict_types=1);

namespace App\Order\Domain;

use App\Order\Domain\Exception\OrderCannotBeConfirmed;
use App\Shared\Domain\Id\CustomerId;
use App\Shared\Domain\Id\OrderId;
use Brick\Math\BigDecimal;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
class Order
{
	/** @var Collection<int, OrderItem> */
	#[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'order', cascade: ['persist'], orphanRemoval: true)]
	private Collection $items;

	public function __construct(
		#[ORM\Id]
		#[ORM\Column(type: 'order_id')]
		public readonly OrderId $id,
		#[ORM\Column(type: 'customer_id')]
		public readonly CustomerId $customerId,
		#[ORM\Column(type: 'string', length: 16, enumType: OrderStatus::class)]
		public private(set) OrderStatus $status,
		#[ORM\Column(type: 'big_decimal', nullable: true)]
		public private(set) BigDecimal|null $totalAmount,
		#[ORM\Column(type: 'datetime_immutable')]
		public readonly \DateTimeImmutable $createdAt,
	) {
		$this->items = new ArrayCollection();
	}

	public function addItem(OrderItem $item): void
	{
		$this->items->add($item);
	}

	/**
	 * @return Collection<int, OrderItem>
	 */
	public function getItems(): Collection
	{
		return $this->items;
	}

	/**
	 * @throws OrderCannotBeConfirmed
	 */
	public function confirm(BigDecimal $totalAmount): void
	{
		if ($this->status !== OrderStatus::DRAFT) {
			throw OrderCannotBeConfirmed::becauseOfCurrentStatus($this->status);
		}

		$this->status = OrderStatus::CONFIRMED;
		$this->totalAmount = $totalAmount;
	}
}
