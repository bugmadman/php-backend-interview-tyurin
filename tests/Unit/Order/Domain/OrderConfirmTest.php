<?php declare(strict_types=1);

namespace Tests\Unit\Order\Domain;

use App\Order\Domain\Exception\OrderCannotBeConfirmed;
use App\Order\Domain\Order;
use App\Order\Domain\OrderStatus;
use App\Shared\Domain\Id\CustomerId;
use App\Shared\Domain\Id\OrderId;
use Brick\Math\BigDecimal;
use PHPUnit\Framework\TestCase;

final class OrderConfirmTest extends TestCase
{
	/**
	 * @throws OrderCannotBeConfirmed
	 */
	public function testConfirmsOrderFromDraftStatus(): void
	{
		$order = $this->createOrder(OrderStatus::DRAFT);

		$order->confirm(BigDecimal::of('123.45'));

		self::assertSame(OrderStatus::CONFIRMED, $order->status);
		self::assertSame('123.45', (string)$order->totalAmount);
	}

	/**
	 * @throws OrderCannotBeConfirmed
	 */
	public function testThrowsWhenOrderIsAlreadyConfirmed(): void
	{
		$order = $this->createOrder(OrderStatus::CONFIRMED);

		$this->expectException(OrderCannotBeConfirmed::class);

		$order->confirm(BigDecimal::of('1.00'));
	}

	/**
	 * @throws OrderCannotBeConfirmed
	 */
	public function testThrowsWhenOrderIsCancelled(): void
	{
		$order = $this->createOrder(OrderStatus::CANCELLED);

		$this->expectException(OrderCannotBeConfirmed::class);

		$order->confirm(BigDecimal::of('1.00'));
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
