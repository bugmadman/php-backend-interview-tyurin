<?php declare(strict_types=1);

namespace App\Shared\Infrastructure\Id\Doctrine;

use App\Shared\Domain\Id\Id;
use Doctrine\DBAL\Types\Type;

/**
 * @template T of Id
 */
abstract class IdType extends Type
{
	/**
	 * @return class-string<T>
	 */
	abstract protected function getIdClassName(): string;

	abstract public function getName(): string;
}
