<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Id\Doctrine;

use App\Shared\Domain\Exception\LogicException;
use App\Shared\Domain\Id\UuidId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Throwable;

/**
 * @template T of UuidId
 * @extends IdType<T>
 *
 * phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
 */
abstract class UuidBinaryIdType extends IdType
{
	public function convertToDatabaseValue($value, AbstractPlatform $platform): string|null
	{
		if ($value === null) {
			return null;
		}

		if ($value instanceof UuidInterface) {
			return $value->getBytes();
		}

		if ($value instanceof UuidId === false) {
			throw LogicException::createForInvalidType(UuidId::class, $value);
		}

		return $value->unwrap()->getBytes();
	}

	/**
	 * @return UuidId|null
	 */
	public function convertToPHPValue($value, AbstractPlatform $platform): UuidId|null
	{
		$class = $this->getIdClassName();

		if ($value instanceof UuidInterface) {
			return $class::fromUuid($value);
		}

		if (\is_a($value, $class)) {
			return $value;
		}

		if (\is_string($value) === false || $value === '') {
			return null;
		}

		try {
			$uuid = Uuid::fromBytes($value);

			return $class::fromUuid($uuid);
		} catch (Throwable $e) {
			throw LogicException::createFromPrevious($e);
		}
	}

	public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
	{
		return $platform->getBinaryTypeDeclarationSQL(
			[
				'length' => '16',
				'fixed' => true,
			],
		);
	}

	/**
	 * @return class-string<T>
	 */
	abstract protected function getIdClassName(): string;
}
