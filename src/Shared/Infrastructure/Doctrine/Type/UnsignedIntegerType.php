<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Type;

use App\Shared\Domain\Exception\LogicException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;

/**
 * phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
 */
final class UnsignedIntegerType extends IntegerType
{
	/**
	 * {@inheritDoc}
	 *
	 * @param T $value
	 * @return (T is null ? null : non-negative-int)
	 * @template T
	 */
	public function convertToPHPValue($value, AbstractPlatform $platform): int|null
	{
		$value = parent::convertToPHPValue($value, $platform);

		if ($value !== null && $value < 0) {
			throw LogicException::createForInvalidType('non-negative-int', $value);
		}

		return $value;
	}
}
