<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Type;

use Brick\Math\BigInteger;
use Brick\Math\Exception\MathException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;

/**
 * phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
 */
final class BigIntegerType extends Type
{
	private const string NAME = 'big_integer';

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
			return BigInteger::of($value);
		} catch (MathException $exception) {
			throw ValueNotConvertible::new($value, self::NAME, previous: $exception);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
	{
		if ($value === null) {
			return null;
		}

		if ($value instanceof BigInteger) {
			return (string)$value;
		}

		throw InvalidType::new($value, self::NAME, [BigInteger::class]);
	}
}
