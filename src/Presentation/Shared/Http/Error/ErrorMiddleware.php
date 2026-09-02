<?php declare(strict_types=1);

namespace App\Presentation\Shared\Http\Error;

use App\Presentation\Shared\Http\StatusCode;
use ErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpNotFoundException;
use Throwable;

final readonly class ErrorMiddleware implements MiddlewareInterface
{
	public function __construct(
		private ErrorResponseFactory $errorResponseFactory,
		private LoggerInterface $logger,
	) {
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		\set_error_handler(static function ($errno, $errstr, $errfile, $errline): bool {
			// ignore @muted errors
			if ((\error_reporting() & $errno) === 0) {
				return false;
			}

			throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
		});

		try {
			/** @throws HttpNotFoundException|Throwable */
			return $handler->handle($request);
		} catch (HttpNotFoundException $e) {
			return $this->errorResponseFactory->createErrorResponse(StatusCode::NOT_FOUND, 'NOT_FOUND', []);
		} catch (Throwable $e) {
			$this->logger->error($e->getMessage(), ['exception' => $e]);

			return $this->errorResponseFactory->createErrorResponse(StatusCode::INTERNAL_SERVER_ERROR, 'INTERNAL_SERVER_ERROR', []);
		} finally {
			\restore_error_handler();
		}
	}
}
