<?php declare(strict_types=1);

namespace Tests\Unit\Inventory\Domain;

use App\Inventory\Domain\Inventory;
use App\Shared\Domain\Id\ProductId;
use PHPUnit\Framework\TestCase;

final class InventoryTest extends TestCase
{
	public function testDecreaseReducesAvailableQuantity(): void
	{
		$inventory = new Inventory(ProductId::generate(), 10);

		$inventory->decrease(3);

		self::assertSame(7, $inventory->available);
	}

	public function testDecreaseByExactAvailableQuantityResultsInZero(): void
	{
		$inventory = new Inventory(ProductId::generate(), 5);

		$inventory->decrease(5);

		self::assertSame(0, $inventory->available);
	}
}
