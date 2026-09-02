<?php declare(strict_types=1);

namespace Tests\Unit\Shared\Domain\Id;

use App\Shared\Domain\Id\CustomerId;
use App\Shared\Domain\Id\OrderId;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class OrderIdTest extends TestCase
{
	public function testGenerateReturnsValidUuid(): void
	{
		$id = OrderId::generate();

		self::assertTrue(Uuid::isValid($id->toString()));
	}

	public function testFromStringRoundTrips(): void
	{
		$uuid = Uuid::uuid7()->toString();

		$id = OrderId::fromString($uuid);

		self::assertSame($uuid, $id->toString());
		self::assertSame($uuid, (string)$id);
	}

	public function testEqualsIsTrueForSameValue(): void
	{
		$uuid = Uuid::uuid7()->toString();

		self::assertTrue(OrderId::fromString($uuid)->equals(OrderId::fromString($uuid)));
	}

	public function testEqualsIsFalseForDifferentValue(): void
	{
		self::assertFalse(OrderId::generate()->equals(OrderId::generate()));
	}

	public function testEqualsIsFalseForDifferentIdTypeWithSameUuid(): void
	{
		$uuid = Uuid::uuid7()->toString();

		self::assertFalse(OrderId::fromString($uuid)->equals(CustomerId::fromString($uuid)));
	}
}
