<?php declare(strict_types=1);

namespace App\Presentation\Shared\Http\Json;

use Psr\Http\Server\RequestHandlerInterface;

interface JsonRequestWithParsedBodyHandler extends RequestHandlerInterface
{
	/**
	 * @return class-string<object>
	 */
	public static function getParsedBodyClassName(): string;
}
