<?php declare(strict_types=1);

namespace Tests\Unit\Order\Domain;

use App\Order\Domain\Discount\DiscountDefinition;
use App\Order\Domain\Exception\DiscountAlreadyApplied;
use App\Order\Domain\Exception\OrderDiscountCannotBeApplied;
use App\Order\Domain\Order;
use App\Order\Domain\OrderDiscountType;
use App\Order\Domain\OrderItem;
use App\Order\Domain\OrderStatus;
use App\Shared\Domain\Exception\LogicException;
use App\Shared\Domain\Id\CustomerId;
use App\Shared\Domain\Id\OrderId;
use App\Shared\Domain\Id\OrderItemId;
use App\Shared\Domain\Id\ProductId;
use Brick\Math\BigDecimal;
use PHPUnit\Framework\TestCase;

final class OrderCalculatePricingPreviewTest extends TestCase
{
	public function testSubtotalEqualsTotalWithoutDiscount(): void
	{
		$order = $this->createOrder();
		$this->addItem($order, '19.99', 2);
		$this->addItem($order, '5.00', 1);

		$preview = $order->calculatePricingPreview();

		self::assertSame('44.98', (string)$preview->subtotal);
		self::assertSame('0.00', (string)$preview->discountTotal);
		self::assertSame('44.98', (string)$preview->total);
	}

	/**
	 * @throws DiscountAlreadyApplied
	 * @throws OrderDiscountCannotBeApplied
	 */
	public function testAppliesPercentDiscountToSubtotal(): void
	{
		$order = $this->createOrder();
		$this->addItem($order, '100.00', 1);
		$order->applyDiscount(new DiscountDefinition('WELCOME10', OrderDiscountType::PERCENT, BigDecimal::of('10.00')));

		$preview = $order->calculatePricingPreview();

		self::assertSame('100.00', (string)$preview->subtotal);
		self::assertSame('10.00', (string)$preview->discountTotal);
		self::assertSame('90.00', (string)$preview->total);
	}

	/**
	 * @throws DiscountAlreadyApplied
	 * @throws OrderDiscountCannotBeApplied
	 */
	public function testFixedDiscountIsCappedAtSubtotalAndTotalNeverGoesNegative(): void
	{
		$order = $this->createOrder();
		$this->addItem($order, '30.00', 1);
		$order->applyDiscount(new DiscountDefinition('SAVE50', OrderDiscountType::FIXED, BigDecimal::of('50.00')));

		$preview = $order->calculatePricingPreview();

		self::assertSame('30.00', (string)$preview->subtotal);
		self::assertSame('30.00', (string)$preview->discountTotal);
		self::assertSame('0.00', (string)$preview->total);
	}

	public function testThrowsWhenItemHasNoUnitPrice(): void
	{
		$order = $this->createOrder();
		$order->addItem(new OrderItem(
			id: OrderItemId::generate(),
			order: $order,
			productId: ProductId::generate(),
			quantity: 1,
			unitPrice: null,
		));

		$this->expectException(LogicException::class);

		$order->calculatePricingPreview();
	}

	private function createOrder(): Order
	{
		return new Order(
			id: OrderId::generate(),
			customerId: CustomerId::generate(),
			status: OrderStatus::DRAFT,
			totalAmount: null,
			createdAt: new \DateTimeImmutable(),
		);
	}

	private function addItem(
		Order $order,
		string $unitPrice,
		int $quantity,
	): void {
		$order->addItem(new OrderItem(
			id: OrderItemId::generate(),
			order: $order,
			productId: ProductId::generate(),
			quantity: $quantity,
			unitPrice: BigDecimal::of($unitPrice),
		));
	}
}
