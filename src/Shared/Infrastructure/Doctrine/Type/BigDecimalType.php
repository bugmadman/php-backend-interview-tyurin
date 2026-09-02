<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Type;

use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;

/**
 * phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
 */
final class BigDecimalType extends Type
{
	private const string NAME = 'big_decimal';

	/**
	 * {@inheritdoc}
	 */
	public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
	{
		return $platform->getDecimalTypeDeclarationSQL($column);
	}

	/**
	 * {@inheritdoc}
	 */
	public function convertToPHPValue($value, AbstractPlatform $platform): mixed
	{
		if ($value === null) {
			return null;
		}

		try {
			return BigDecimal::of($value);
		} catch (MathException $exception) {
			throw ValueNotConvertible::new($value, self::NAME, previous: $exception);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): mixed
	{
		if ($value === null) {
			return null;
		}

		if ($value instanceof BigDecimal) {
			return (string)$value;
		}

		throw InvalidType::new($value, self::NAME, [BigDecimal::class]);
	}
}
