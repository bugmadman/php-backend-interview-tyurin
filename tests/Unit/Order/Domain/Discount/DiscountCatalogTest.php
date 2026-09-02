<?php declare(strict_types=1);

namespace Tests\Unit\Order\Domain\Discount;

use App\Order\Domain\Discount\DiscountCatalog;
use App\Order\Domain\OrderDiscountType;
use PHPUnit\Framework\TestCase;

final class DiscountCatalogTest extends TestCase
{
	public function testFindsPercentDiscountByCode(): void
	{
		$definition = (new DiscountCatalog())->find('WELCOME10');

		self::assertNotNull($definition);
		self::assertSame('WELCOME10', $definition->code);
		self::assertSame(OrderDiscountType::PERCENT, $definition->type);
		self::assertSame('10.00', (string)$definition->value);
	}

	public function testFindsFixedDiscountByCode(): void
	{
		$definition = (new DiscountCatalog())->find('SAVE50');

		self::assertNotNull($definition);
		self::assertSame('SAVE50', $definition->code);
		self::assertSame(OrderDiscountType::FIXED, $definition->type);
		self::assertSame('50.00', (string)$definition->value);
	}

	public function testReturnsNullForUnknownCode(): void
	{
		self::assertNull((new DiscountCatalog())->find('UNKNOWN'));
	}

	public function testCodeLookupIsCaseSensitive(): void
	{
		self::assertNull((new DiscountCatalog())->find('welcome10'));
	}
}
