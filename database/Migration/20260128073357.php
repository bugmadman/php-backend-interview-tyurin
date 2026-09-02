<?php declare(strict_types=1);

namespace Database\Migration;

use Phinx\Migration\AbstractMigration;

final class V20260128073357 extends AbstractMigration
{
	public function up(): void
	{
		$this->execute(
			<<<'SQL'
                    CREATE TABLE customers (
                      id           UUID PRIMARY KEY,
                      email        VARCHAR(255) NOT NULL,
                      country      VARCHAR(32),
                      created_at   TIMESTAMP NOT NULL DEFAULT NOW()
                    );
                    
                    CREATE TABLE products (
                      id         UUID PRIMARY KEY,
                      name       VARCHAR(255) NOT NULL,
                      category   VARCHAR(64) NOT NULL,
                      price      NUMERIC(10,2) NOT NULL
                    );
                    
                    CREATE TABLE inventory (
                      product_id  UUID PRIMARY KEY REFERENCES products(id),
                      available   INT NOT NULL
                    );
                    
                    CREATE TABLE orders (
                      id           UUID PRIMARY KEY,
                      customer_id  UUID NOT NULL REFERENCES customers(id),
                      status       VARCHAR(16) NOT NULL,
                      total_amount NUMERIC(10,2),
                      created_at   TIMESTAMP NOT NULL DEFAULT NOW()
                    );
                    
                    CREATE TABLE order_items (
                      id         UUID PRIMARY KEY,
                      order_id   UUID NOT NULL REFERENCES orders(id),
                      product_id UUID  NOT NULL REFERENCES products(id),
                      quantity   INT  NOT NULL,
                      unit_price NUMERIC(10,2)
                    );
                    
                    CREATE INDEX idx_orders_customer_created ON orders(customer_id, created_at);
                    CREATE INDEX idx_orders_status_created ON orders(status, created_at);
SQL,
		);
	}
}
