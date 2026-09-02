<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Doctrine\Type;

use App\Shared\Domain\Exception\LogicException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Throwable;

/**
 * phpcs:disable Generic.NamingConventions.CamelCapsFunctionName.ScopeNotCamelCaps
 */
final class UuidType extends Type
{
	public const string NAME = 'uuid';

	public function getName(): string
	{
		return self::NAME;
	}

	public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
	{
		return $platform->getGuidTypeDeclarationSQL($column);
	}

	public function convertToDatabaseValue($value, AbstractPlatform $platform): string|null
	{
		if ($value === null) {
			return null;
		}

		if ($value instanceof UuidInterface) {
			return $value->toString();
		}

		throw LogicException::createForInvalidType(UuidInterface::class, $value);
	}

	public function convertToPHPValue($value, AbstractPlatform $platform): UuidInterface|null
	{
		if ($value instanceof UuidInterface) {
			return $value;
		}

		if (!\is_string($value) || $value === '') {
			return null;
		}

		try {
			return Uuid::fromString($value);
		} catch (Throwable $e) {
			throw LogicException::createFromPrevious($e);
		}
	}

	public function requiresSQLCommentHint(AbstractPlatform $platform): bool
	{
		return true;
	}
}
