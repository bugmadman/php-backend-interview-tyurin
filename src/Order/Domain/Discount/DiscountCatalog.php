<?php declare(strict_types=1);

namespace App\Order\Domain\Discount;

use App\Order\Domain\OrderDiscountType;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

final readonly class DiscountCatalog
{
	private const array DEFINITIONS = [
		'WELCOME10' => ['type' => OrderDiscountType::PERCENT, 'value' => '10'],
		'SAVE50' => ['type' => OrderDiscountType::FIXED, 'value' => '50'],
	];

	public function find(string $normalizedCode): DiscountDefinition|null
	{
		$definition = self::DEFINITIONS[$normalizedCode] ?? null;

		if ($definition === null) {
			return null;
		}

		return new DiscountDefinition(
			$normalizedCode,
			$definition['type'],
			BigDecimal::of($definition['value'])->toScale(2, RoundingMode::HALF_UP),
		);
	}
}
