<?php declare(strict_types=1);

namespace Database\Seed;

use Phinx\Seed\AbstractSeed;
use Ramsey\Uuid\Uuid;

final class InitSeeder extends AbstractSeed
{
	public function run(): void
	{
		$customerAlice = Uuid::uuid7()->toString();
		$customerBob   = Uuid::uuid7()->toString();
		$customerCarol = Uuid::uuid7()->toString();

		$productWhey   = Uuid::uuid7()->toString();
		$productCrea   = Uuid::uuid7()->toString();
		$productOmega  = Uuid::uuid7()->toString();
		$productBar    = Uuid::uuid7()->toString();
		$productShaker = Uuid::uuid7()->toString();

		$order1 = Uuid::uuid7()->toString(); // draft
		$order2 = Uuid::uuid7()->toString(); // confirmed
		$order3 = Uuid::uuid7()->toString(); // cancelled (has NULL unit_price item)
		$order4 = Uuid::uuid7()->toString(); // confirmed

		$this->table('customers')->insert([
			[
				'id' => $customerAlice,
				'email' => 'alice@example.com',
				'country' => 'CZ',
				'created_at' => '2024-01-05 10:00:00',
			],
			[
				'id' => $customerBob,
				'email' => 'bob@example.com',
				'country' => 'SK',
				'created_at' => '2024-02-10 12:30:00',
			],
			[
				'id' => $customerCarol,
				'email' => 'carol@example.com',
				'country' => null,
				'created_at' => '2024-03-01 09:15:00',
			],
		])->saveData();

		$this->table('products')->insert([
			['id' => $productWhey,   'name' => 'Whey Protein 1kg', 'category' => 'protein',    'price' => 699.00],
			['id' => $productCrea,   'name' => 'Creatine 500g',    'category' => 'supplement', 'price' => 399.00],
			['id' => $productOmega,  'name' => 'Omega 3',          'category' => 'supplement', 'price' => 249.00],
			['id' => $productBar,    'name' => 'Protein Bar',      'category' => 'snack',      'price' => 49.00],
			['id' => $productShaker, 'name' => 'Shaker',           'category' => 'accessory',  'price' => 129.00],
		])->saveData();

		$this->table('inventory')->insert([
			['product_id' => $productWhey,   'available' => 50],
			['product_id' => $productCrea,   'available' => 5],
			['product_id' => $productOmega,  'available' => 0],   // out-of-stock edge
			['product_id' => $productBar,    'available' => 200],
			['product_id' => $productShaker, 'available' => 10],
		])->saveData();

		$this->table('orders')->insert([
			[
				'id' => $order1,
				'customer_id' => $customerAlice,
				'status' => 'draft',
				'total_amount' => null,
				'created_at' => '2024-03-10 11:00:00',
			],
			[
				'id' => $order2,
				'customer_id' => $customerAlice,
				'status' => 'confirmed',
				'total_amount' => 927.00, // 2*399 + 1*129
				'created_at' => '2024-03-12 08:00:00',
			],
			[
				'id' => $order3,
				'customer_id' => $customerBob,
				'status' => 'cancelled',
				'total_amount' => null,
				'created_at' => '2024-04-01 14:20:00',
			],
			[
				'id' => $order4,
				'customer_id' => $customerCarol,
				'status' => 'confirmed',
				'total_amount' => 98.00, // 2*49
				'created_at' => '2024-04-05 09:00:00',
			],
		])->saveData();

		$this->table('order_items')->insert([
			// order1 (draft)
			[
				'id' => Uuid::uuid7()->toString(),
				'order_id' => $order1,
				'product_id' => $productBar,
				'quantity' => 2,
				'unit_price' => 49.00,
			],

			// order2 (confirmed) total 927
			[
				'id' => Uuid::uuid7()->toString(),
				'order_id' => $order2,
				'product_id' => $productCrea,
				'quantity' => 2,
				'unit_price' => 399.00,
			],
			[
				'id' => Uuid::uuid7()->toString(),
				'order_id' => $order2,
				'product_id' => $productShaker,
				'quantity' => 1,
				'unit_price' => 129.00,
			],

			// order3 (cancelled) data quality: NULL unit_price
			[
				'id' => Uuid::uuid7()->toString(),
				'order_id' => $order3,
				'product_id' => $productWhey,
				'quantity' => 1,
				'unit_price' => null, // intentionally broken row
			],

			// order4 (confirmed) total 98
			[
				'id' => Uuid::uuid7()->toString(),
				'order_id' => $order4,
				'product_id' => $productBar,
				'quantity' => 2,
				'unit_price' => 49.00,
			],
		])->saveData();
	}
}
