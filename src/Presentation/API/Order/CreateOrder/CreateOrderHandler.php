<?php declare(strict_types=1);

namespace App\Presentation\API\Order\CreateOrder;

use App\Customer\Infrastructure\Doctrine\CustomerRepository;
use App\Order\Domain\Order;
use App\Order\Domain\OrderItem;
use App\Order\Domain\OrderStatus;
use App\Presentation\API\Order\Response\OrderResponse;
use App\Presentation\Shared\Http\Error\ErrorResponseFactory;
use App\Presentation\Shared\Http\Json\JsonRequestWithParsedBodyHandler;
use App\Presentation\Shared\Http\Json\JsonResponseFactory;
use App\Presentation\Shared\Http\StatusCode;
use App\Product\Domain\Product;
use App\Product\Infrastructure\Doctrine\ProductRepository;
use App\Shared\Domain\Id\CustomerId;
use App\Shared\Domain\Id\OrderId;
use App\Shared\Domain\Id\OrderItemId;
use App\Shared\Domain\Id\ProductId;
use App\Shared\Infrastructure\Doctrine\ManagerRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

final readonly class CreateOrderHandler implements JsonRequestWithParsedBodyHandler
{
	public function __construct(
		private JsonResponseFactory $jsonResponseFactory,
		private ErrorResponseFactory $errorResponseFactory,
		private CustomerRepository $customerRepository,
		private ProductRepository $productRepository,
		private ManagerRegistry $managerRegistry,
	) {
	}

	public static function getParsedBodyClassName(): string
	{
		return CreateOrderRequest::class;
	}

	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$body = $request->getParsedBody();
		\assert($body instanceof CreateOrderRequest);

		$customerId = $this->parseUuid($body->customerId);

		if ($customerId === null) {
			return $this->errorResponseFactory->createUnprocessableEntity('INVALID_CUSTOMER_ID');
		}

		$customer = $this->customerRepository->find(CustomerId::fromString($customerId));

		if ($customer === null) {
			return $this->errorResponseFactory->createErrorResponse(StatusCode::NOT_FOUND, 'CUSTOMER_NOT_FOUND', []);
		}

		if ($body->items === []) {
			return $this->errorResponseFactory->createUnprocessableEntity('EMPTY_ITEMS');
		}

		/** @var list<array{productIdRaw: mixed, productId: string|null, quantity: int}> $items */
		$items = [];

		foreach ($body->items as $rawItem) {
			$quantity = \is_array($rawItem) ? ($rawItem['quantity'] ?? null) : null;

			if (\is_int($quantity) === false || $quantity < 1) {
				return $this->errorResponseFactory->createUnprocessableEntity('INVALID_QUANTITY');
			}

			$productIdRaw = \is_array($rawItem) ? ($rawItem['product_id'] ?? null) : null;
			$items[] = [
				'productIdRaw' => $productIdRaw,
				'productId' => $this->parseUuid($productIdRaw),
				'quantity' => $quantity,
			];
		}

		/** @var array<string, true> $uniqueProductIds */
		$uniqueProductIds = [];

		foreach ($items as $item) {
			if ($item['productId'] !== null) {
				$uniqueProductIds[$item['productId']] = true;
			}
		}

		$products = $this->productRepository->findByIds(\array_map(
			static fn (string $id): ProductId => ProductId::fromString($id),
			\array_keys($uniqueProductIds),
		));

		/** @var list<array{product: Product, quantity: int}> $orderItems */
		$orderItems = [];

		foreach ($items as $item) {
			$product = $item['productId'] !== null ? ($products[$item['productId']] ?? null) : null;

			if ($product === null) {
				return $this->errorResponseFactory->createUnprocessableEntity('UNKNOWN_PRODUCT', ['product_id' => $this->stringifyProductId($item['productIdRaw'])]);
			}

			$orderItems[] = ['product' => $product, 'quantity' => $item['quantity']];
		}

		$order = new Order(
			id: OrderId::generate(),
			customerId: CustomerId::fromString($customerId),
			status: OrderStatus::DRAFT,
			totalAmount: null,
			createdAt: new \DateTimeImmutable(),
		);

		foreach ($orderItems as $orderItem) {
			$order->addItem(new OrderItem(
				id: OrderItemId::generate(),
				order: $order,
				productId: $orderItem['product']->id,
				quantity: $orderItem['quantity'],
				unitPrice: $orderItem['product']->price,
			));
		}

		$manager = $this->managerRegistry->getManager();
		$manager->persist($order);
		$manager->flush();

		return $this->jsonResponseFactory->create(OrderResponse::fromEntity($order), StatusCode::CREATED);
	}

	private function parseUuid(mixed $value): string|null
	{
		if (\is_string($value) === false || Uuid::isValid($value) === false) {
			return null;
		}

		return $value;
	}

	private function stringifyProductId(mixed $value): string|null
	{
		return \is_scalar($value) ? (string)$value : null;
	}
}
