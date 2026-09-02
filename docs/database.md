# Database

Postgres 16. Schema is managed by **Phinx** migrations under
[database/Migration/](../database/Migration/) — not Doctrine Migrations, see [CLAUDE.md](../CLAUDE.md).

`make db-migrate` / `make db-rollback` / `make db-seed` apply migrations and seed data; `make db-create
args="Name"` scaffolds a new migration.

## Tables

### customers
| column       | type          | notes                |
|--------------|---------------|-----------------------|
| `id`         | UUID PK       |                       |
| `email`      | VARCHAR(255)  | NOT NULL              |
| `country`    | VARCHAR(32)   |                       |
| `created_at` | TIMESTAMP     | NOT NULL, default now |

### products
| column     | type          | notes   |
|------------|---------------|---------|
| `id`       | UUID PK       |         |
| `name`     | VARCHAR(255)  | NOT NULL |
| `category` | VARCHAR(64)   | NOT NULL |
| `price`    | NUMERIC(10,2) | NOT NULL |

### inventory
1:1 with `products`.

| column       | type    | notes                   |
|--------------|---------|-------------------------|
| `product_id` | UUID PK | REFERENCES products(id) |
| `available`  | INT     | NOT NULL                |

### orders
| column           | type          | notes                                    |
|------------------|---------------|-------------------------------------------|
| `id`             | UUID PK       |                                            |
| `customer_id`    | UUID          | NOT NULL, REFERENCES customers(id)        |
| `status`         | VARCHAR(16)   | NOT NULL — `draft` / `confirmed` / `cancelled` |
| `total_amount`   | NUMERIC(10,2) |                                            |
| `created_at`     | TIMESTAMP     | NOT NULL, default now                     |
| `discount_code`  | VARCHAR(32)   | nullable — added for the discount feature |
| `discount_type`  | VARCHAR(16)   | nullable                                  |
| `discount_value` | NUMERIC(10,2) | nullable                                  |

Indexes: `(customer_id, created_at)`, `(status, created_at)`.

### order_items
| column       | type          | notes                     |
|--------------|---------------|----------------------------|
| `id`         | UUID PK       |                            |
| `order_id`   | UUID          | NOT NULL, REFERENCES orders(id) |
| `product_id` | UUID          | NOT NULL, REFERENCES products(id) |
| `quantity`   | INT           | NOT NULL                  |
| `unit_price` | NUMERIC(10,2) |                            |

## Relations

```
customers 1───* orders 1───* order_items *───1 products 1───1 inventory
```

`orders.status` lifecycle: `draft` → `confirmed` (via `POST /api/orders/{id}/confirm`, which also
decrements `inventory.available`) or `draft` → `cancelled`.

Discount codes (`WELCOME10`, `SAVE50`) are currently hardcoded in
[DiscountCatalog.php](../src/Order/Domain/Discount/DiscountCatalog.php), not in a `discount_codes` table
— see [TODO.md](../TODO.md#2-discountcatalogdefinitions--hardcoded-instead-of-a-table) for the reasoning
and what moving this to the DB would take.
