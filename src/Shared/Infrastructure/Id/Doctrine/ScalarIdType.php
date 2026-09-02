<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Id\Doctrine;

use App\Shared\Domain\Exception\LogicException;
use App\Shared\Domain\Id\ScalarId;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * @template T of ScalarId
 * @extends IdType<T>
 *
 * phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
 */
abstract class ScalarIdType extends IdType
{
	/**
	 * @return ScalarId<int|string>|null
	 */
	public function convertToPHPValue($value, $platform): ScalarId|null
	{
		if ($value === null) {
			return null;
		}

		if (\is_object($value) || \is_array($value)) {
			throw LogicException::createForInvalidType('scalar', $value);
		}

		$class = $this->getIdClassName();

		return $class::from($value);
	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
	{
		if ($value === null) {
			return null;
		}

		if ($value instanceof ScalarId === false) {
			throw LogicException::createForInvalidType('ScalarId', $value);
		}

		return $value->unwrap();
	}

	public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
	{
		return $platform->getIntegerTypeDeclarationSQL($column);
	}
}
