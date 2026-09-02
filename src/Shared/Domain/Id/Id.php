<?php declare(strict_types=1);

namespace App\Shared\Domain\Id;

use JsonSerializable;
use Stringable;

/**
 * @template T
 */
interface Id extends Stringable, JsonSerializable
{
	/**
	 * @template TId of Id
	 * @param TId $other
	 */
	public function equals(Id $other): bool;

	/**
	 * @return T
	 */
	public function unwrap(): mixed;
}
