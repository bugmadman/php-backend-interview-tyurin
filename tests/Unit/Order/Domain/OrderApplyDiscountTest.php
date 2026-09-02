<?php declare(strict_types=1);

namespace Tests\Unit\Order\Domain;

use App\Order\Domain\Discount\DiscountDefinition;
use App\Order\Domain\Exception\DiscountAlreadyApplied;
use App\Order\Domain\Exception\OrderDiscountCannotBeApplied;
use App\Order\Domain\Order;
use App\Order\Domain\OrderDiscountType;
use App\Order\Domain\OrderStatus;
use App\Shared\Domain\Id\CustomerId;
use App\Shared\Domain\Id\OrderId;
use Brick\Math\BigDecimal;
use PHPUnit\Framework\TestCase;

final class OrderApplyDiscountTest extends TestCase
{
	/**
	 * @throws DiscountAlreadyApplied
	 * @throws OrderDiscountCannotBeApplied
	 */
	public function testAppliesDiscountToDraftOrder(): void
	{
		$order = $this->createOrder(OrderStatus::DRAFT);
		$discount = new DiscountDefinition('WELCOME10', OrderDiscountType::PERCENT, BigDecimal::of('10.00'));

		$order->applyDiscount($discount);

		self::assertTrue($order->hasDiscount());
		self::assertSame('WELCOME10', $order->discountCode);
		self::assertSame(OrderDiscountType::PERCENT, $order->discountType);
		self::assertSame('10.00', (string)$order->discountValue);
	}

	/**
	 * @throws DiscountAlreadyApplied
	 * @throws OrderDiscountCannotBeApplied
	 */
	public function testThrowsWhenOrderIsNotDraft(): void
	{
		$order = $this->createOrder(OrderStatus::CONFIRMED);
		$discount = new DiscountDefinition('WELCOME10', OrderDiscountType::PERCENT, BigDecimal::of('10.00'));

		$this->expectException(OrderDiscountCannotBeApplied::class);

		$order->applyDiscount($discount);
	}

	/**
	 * @throws DiscountAlreadyApplied
	 * @throws OrderDiscountCannotBeApplied
	 */
	public function testThrowsWhenDiscountIsAlreadyApplied(): void
	{
		$order = $this->createOrder(OrderStatus::DRAFT);
		$order->applyDiscount(new DiscountDefinition('WELCOME10', OrderDiscountType::PERCENT, BigDecimal::of('10.00')));

		$this->expectException(DiscountAlreadyApplied::class);

		$order->applyDiscount(new DiscountDefinition('SAVE50', OrderDiscountType::FIXED, BigDecimal::of('50.00')));
	}

	public function testHasDiscountIsFalseByDefault(): void
	{
		$order = $this->createOrder(OrderStatus::DRAFT);

		self::assertFalse($order->hasDiscount());
	}

	private function createOrder(OrderStatus $status): Order
	{
		return new Order(
			id: OrderId::generate(),
			customerId: CustomerId::generate(),
			status: $status,
			totalAmount: null,
			createdAt: new \DateTimeImmutable(),
		);
	}
}
