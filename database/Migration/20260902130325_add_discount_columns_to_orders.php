<?php declare(strict_types=1);

namespace Database\Migration;

use Phinx\Migration\AbstractMigration;

final class AddDiscountColumnsToOrders extends AbstractMigration
{
	public function change(): void
	{
		$this->table('orders')
			->addColumn('discount_code', 'string', ['limit' => 32, 'null' => true])
			->addColumn('discount_type', 'string', ['limit' => 16, 'null' => true])
			->addColumn('discount_value', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => true])
			->update();
	}
}
