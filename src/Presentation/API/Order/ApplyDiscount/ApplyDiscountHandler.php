<?php declare(strict_types=1);

namespace App\Presentation\API\Order\ApplyDiscount;

use App\Order\Domain\Discount\DiscountCatalog;
use App\Order\Domain\Exception\DiscountAlreadyApplied;
use App\Order\Domain\Exception\OrderDiscountCannotBeApplied;
use App\Order\Domain\OrderStatus;
use App\Order\Infrastructure\Doctrine\OrderRepository;
use App\Presentation\Shared\Http\Error\ErrorResponseFactory;
use App\Presentation\Shared\Http\Json\JsonRequestWithParsedBodyHandler;
use App\Presentation\Shared\Http\Json\JsonResponseFactory;
use App\Presentation\Shared\Http\StatusCode;
use App\Shared\Domain\Id\OrderId;
use App\Shared\Infrastructure\Doctrine\ManagerRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Slim\Routing\RouteContext;

final readonly class ApplyDiscountHandler implements JsonRequestWithParsedBodyHandler
{
	public function __construct(
		private JsonResponseFactory $jsonResponseFactory,
		private ErrorResponseFactory $errorResponseFactory,
		private OrderRepository $orderRepository,
		private DiscountCatalog $discountCatalog,
		private ManagerRegistry $managerRegistry,
	) {
	}

	public static function getParsedBodyClassName(): string
	{
		return ApplyDiscountRequest::class;
	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$route = RouteContext::fromRequest($request)->getRoute();
		\assert($route !== null);

		$rawOrderId = $route->getArgument('id');

		if ($rawOrderId === null || Uuid::isValid($rawOrderId) === false) {
			return $this->errorResponseFactory->createErrorResponse(StatusCode::BAD_REQUEST, 'INVALID_ORDER_ID', []);
		}

		$body = $request->getParsedBody();
		\assert($body instanceof ApplyDiscountRequest);

		$orderId = OrderId::fromString($rawOrderId);

		try {
			$response = $this->managerRegistry->getManager()->wrapInTransaction(
				fn (): ResponseInterface|null => $this->applyDiscount($orderId, $body),
			);
		} catch (OrderDiscountCannotBeApplied) {
			return $this->errorResponseFactory->createConflict('ORDER_INVALID_STATE');
		} catch (DiscountAlreadyApplied) {
			return $this->errorResponseFactory->createConflict('DISCOUNT_ALREADY_APPLIED');
		}

		if ($response === null) {
			return $this->errorResponseFactory->createErrorResponse(StatusCode::NOT_FOUND, 'ORDER_NOT_FOUND', []);
		}

		return $response;
	}

	/**
	 * @throws OrderDiscountCannotBeApplied
	 * @throws DiscountAlreadyApplied
	 */
	private function applyDiscount(OrderId $orderId, ApplyDiscountRequest $body): ResponseInterface|null
	{
		$order = $this->orderRepository->findForUpdate($orderId);

		if ($order === null) {
			return null;
		}

		if ($order->status !== OrderStatus::DRAFT) {
			throw OrderDiscountCannotBeApplied::becauseOfCurrentStatus($order->status);
		}

		if (\is_string($body->code) === false || \trim($body->code) === '') {
			return $this->errorResponseFactory->createUnprocessableEntity('INVALID_DISCOUNT_CODE');
		}

		$normalizedCode = \mb_strtoupper(\trim($body->code));
		$discountDefinition = $this->discountCatalog->find($normalizedCode);

		if ($discountDefinition === null) {
			return $this->errorResponseFactory->createErrorResponse(StatusCode::NOT_FOUND, 'DISCOUNT_NOT_FOUND', []);
		}

		$order->applyDiscount($discountDefinition);
		$pricingPreview = $order->calculatePricingPreview();

		return $this->jsonResponseFactory->create(ApplyDiscountResponse::fromOrder($order, $pricingPreview));
	}
}
