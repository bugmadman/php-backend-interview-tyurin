<?php declare(strict_types=1);

namespace App\Presentation\API\Hello\GetHello;

use App\Presentation\Shared\Http\Json\JsonResponseFactory;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[OA\Get(
	path: '/hello',
	operationId: 'getHello',
	summary: 'Get hello world',
	tags: ['Hello'],
	responses: [
		new OA\Response(
			response: 200,
			description: 'Successful hello world response',
			content: new OA\JsonContent(
				properties: [
					new OA\Property(property: 'hello', type: 'string', example: 'world'),
				],
				type: 'object',
			),
		),
	],
)]
final readonly class GetHelloHandler implements RequestHandlerInterface
{
	public function __construct(
		private JsonResponseFactory $jsonResponseFactory,
	) {
	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		return $this->jsonResponseFactory->create(['hello' => 'world']);
	}
}
