<?php declare(strict_types=1);

namespace App\Order\Domain;

use App\Order\Domain\Discount\DiscountDefinition;
use App\Order\Domain\Discount\PricingPreview;
use App\Order\Domain\Exception\DiscountAlreadyApplied;
use App\Order\Domain\Exception\OrderCannotBeConfirmed;
use App\Order\Domain\Exception\OrderDiscountCannotBeApplied;
use App\Shared\Domain\Exception\LogicException;
use App\Shared\Domain\Id\CustomerId;
use App\Shared\Domain\Id\OrderId;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'orders')]
class Order
{
	#[ORM\Column(type: 'string', length: 32, nullable: true)]
	private(set) string|null $discountCode = null;

	#[ORM\Column(type: 'string', length: 16, nullable: true, enumType: OrderDiscountType::class)]
	private(set) OrderDiscountType|null $discountType = null;

	#[ORM\Column(type: 'big_decimal', nullable: true)]
	private(set) BigDecimal|null $discountValue = null;

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

	/**
	 * @throws OrderDiscountCannotBeApplied
	 * @throws DiscountAlreadyApplied
	 */
	public function applyDiscount(DiscountDefinition $discount): void
	{
		if ($this->status !== OrderStatus::DRAFT) {
			throw OrderDiscountCannotBeApplied::becauseOfCurrentStatus($this->status);
		}

		if ($this->hasDiscount()) {
			throw DiscountAlreadyApplied::forOrder($this->id);
		}

		$this->discountCode = $discount->code;
		$this->discountType = $discount->type;
		$this->discountValue = $discount->value;
	}

	public function hasDiscount(): bool
	{
		return $this->discountCode !== null;
	}

	public function calculatePricingPreview(): PricingPreview
	{
		$subtotal = BigDecimal::zero();

		foreach ($this->items as $item) {
			if ($item->unitPrice === null) {
				throw LogicException::createForNullValue('order->getItems()->unitPrice');
			}

			$subtotal = $subtotal->plus($item->unitPrice->multipliedBy($item->quantity));
		}

		$subtotal = $subtotal->toScale(2, RoundingMode::HALF_UP);

		if ($this->discountType === null || $this->discountValue === null) {
			return new PricingPreview($subtotal, BigDecimal::zero()->toScale(2, RoundingMode::HALF_UP), $subtotal);
		}

		$discount = match ($this->discountType) {
			OrderDiscountType::PERCENT => $subtotal->multipliedBy($this->discountValue)->dividedBy(100, 2, RoundingMode::HALF_UP),
			OrderDiscountType::FIXED => $this->discountValue->toScale(2, RoundingMode::HALF_UP),
		};

		$discountTotal = BigDecimal::min($subtotal, $discount)->toScale(2, RoundingMode::HALF_UP);
		$total = BigDecimal::max(BigDecimal::zero(), $subtotal->minus($discountTotal))->toScale(2, RoundingMode::HALF_UP);

		return new PricingPreview($subtotal, $discountTotal, $total);
	}
}
