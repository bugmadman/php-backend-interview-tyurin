<?php declare(strict_types=1);

namespace App\Presentation\API\Order\ConfirmOrder;

use App\Inventory\Domain\Exception\InventoryIsMissing;
use App\Inventory\Domain\Exception\ProductIsOutOfStock;
use App\Inventory\Domain\Inventory;
use App\Inventory\Infrastructure\Doctrine\InventoryRepository;
use App\Order\Domain\Exception\OrderCannotBeConfirmed;
use App\Order\Domain\OrderStatus;
use App\Order\Infrastructure\Doctrine\OrderRepository;
use App\Presentation\Shared\Http\Error\ErrorResponseFactory;
use App\Presentation\Shared\Http\Json\JsonResponseFactory;
use App\Presentation\Shared\Http\StatusCode;
use App\Shared\Domain\Exception\LogicException;
use App\Shared\Domain\Id\OrderId;
use App\Shared\Domain\Id\ProductId;
use App\Shared\Infrastructure\Doctrine\ManagerRegistry;
use Brick\Math\BigDecimal;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Slim\Routing\RouteContext;

final readonly class ConfirmOrderHandler implements RequestHandlerInterface
{
	public function __construct(
		private JsonResponseFactory $jsonResponseFactory,
		private ErrorResponseFactory $errorResponseFactory,
		private OrderRepository $orderRepository,
		private InventoryRepository $inventoryRepository,
		private ManagerRegistry $managerRegistry,
	) {
	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$route = RouteContext::fromRequest($request)->getRoute();
		\assert($route !== null);

		$rawOrderId = $route->getArgument('id');

		if ($rawOrderId === null || Uuid::isValid($rawOrderId) === false) {
			return $this->errorResponseFactory->createErrorResponse(StatusCode::BAD_REQUEST, 'INVALID_ORDER_ID', []);
		}

		$orderId = OrderId::fromString($rawOrderId);

		try {
			$response = $this->managerRegistry->getManager()->wrapInTransaction(
				fn (): ResponseInterface|null => $this->confirmOrder($orderId),
			);
		} catch (OrderCannotBeConfirmed) {
			return $this->errorResponseFactory->createConflict('ORDER_INVALID_STATE');
		} catch (InventoryIsMissing $e) {
			return $this->errorResponseFactory->createConflict('INVENTORY_MISSING', ['product_id' => $e->productId->toString()]);
		} catch (ProductIsOutOfStock $e) {
			return $this->errorResponseFactory->createConflict('OUT_OF_STOCK', [
				'product_id' => $e->productId->toString(),
				'missing' => $e->missing,
			]);
		}

		if ($response === null) {
			return $this->errorResponseFactory->createErrorResponse(StatusCode::NOT_FOUND, 'ORDER_NOT_FOUND', []);
		}

		return $response;
	}

	/**
	 * @throws OrderCannotBeConfirmed
	 * @throws InventoryIsMissing
	 * @throws ProductIsOutOfStock
	 */
	private function confirmOrder(OrderId $orderId): ResponseInterface|null
	{
		$order = $this->orderRepository->findForUpdate($orderId);

		if ($order === null) {
			return null;
		}

		if ($order->status !== OrderStatus::DRAFT) {
			throw OrderCannotBeConfirmed::becauseOfCurrentStatus($order->status);
		}

		/** @var array<string, int> $requiredQuantityByProductId */
		$requiredQuantityByProductId = [];

		foreach ($order->getItems() as $item) {
			$key = $item->productId->toString();
			$requiredQuantityByProductId[$key] = ($requiredQuantityByProductId[$key] ?? 0) + $item->quantity;
		}

		\ksort($requiredQuantityByProductId);

		/** @var array<string, Inventory> $inventories */
		$inventories = [];

		foreach ($requiredQuantityByProductId as $productIdString => $requiredQuantity) {
			$productId = ProductId::fromString($productIdString);
			$inventory = $this->inventoryRepository->findForUpdate($productId);

			if ($inventory === null) {
				throw InventoryIsMissing::forProduct($productId);
			}

			if ($inventory->available < $requiredQuantity) {
				throw ProductIsOutOfStock::forProduct($productId, $requiredQuantity - $inventory->available);
			}

			$inventories[$productIdString] = $inventory;
		}

		foreach ($requiredQuantityByProductId as $productIdString => $requiredQuantity) {
			$inventories[$productIdString]->decrease($requiredQuantity);
		}

		$totalAmount = BigDecimal::zero();

		foreach ($order->getItems() as $item) {
			if ($item->unitPrice === null) {
				throw LogicException::createForNullValue('order->getItems()->unitPrice');
			}

			$totalAmount = $totalAmount->plus($item->unitPrice->multipliedBy($item->quantity));
		}

		$order->confirm($totalAmount);

		return $this->jsonResponseFactory->create(ConfirmOrderResponse::fromEntity($order));
	}
}
