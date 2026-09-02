# Vilgain PHP Backend Interview Task

A small order API built for Vilgain's PHP backend interview case study (see
[docs/Tyurin - PHP Backend Case Study.pdf](docs/Tyurin%20-%20PHP%20Backend%20Case%20Study.pdf)): create an
order, confirm it against inventory, and price it with a discount code applied.

Stack: **Slim 4** (PSR-15 routing/middleware), **Nette DI** (`.neon` config), **Doctrine ORM/DBAL** on
**Postgres**, **Symfony Serializer** for JSON (de)serialization, **Phinx** for migrations, **PHPUnit** for
tests. No MVC controllers — each endpoint is a vertical slice (Handler + Router) under
`src/Presentation/API/`; details in [CLAUDE.md](CLAUDE.md).

## Quick start
Requirements: Docker + Docker Compose

1) `make reset`
2) API: http://localhost:8000

## API

All endpoints live under `/api/orders` (see [OrderRouter.php](src/Presentation/API/Order/OrderRouter.php)):

| Method | Path                          | Purpose                                                              |
|--------|-------------------------------|-----------------------------------------------------------------------|
| POST   | `/api/orders`                 | Create a draft order from a customer + a list of product/quantity items |
| POST   | `/api/orders/{id}/confirm`    | Confirm a draft order: checks & decrements inventory, locks in the total |
| POST   | `/api/orders/{id}/apply-discount` | Apply a discount code to an order and return a pricing preview (subtotal / discount / total) |

An order moves through `draft → confirmed` (or `cancelled`), see
[OrderStatus.php](src/Order/Domain/OrderStatus.php). Discount codes are currently hardcoded in
[DiscountCatalog.php](src/Order/Domain/Discount/DiscountCatalog.php) (`WELCOME10` = 10%, `SAVE50` = 50
fixed) — see [TODO.md](TODO.md#2-discountcatalogdefinitions--hardcoded-instead-of-a-table) for why, and
what moving this to the DB would take.

## Docs

- [docs/database.md](docs/database.md) — schema, tables, relations.
- [TODO.md](TODO.md) — open questions and discussion points from the code review.
- [CLAUDE.md](CLAUDE.md) — project architecture, conventions and tooling (written for Claude Code, doubles as dev docs).

## Development

- `make phpcs` / `make phpcs-fix` — coding standard check/fix
- `make phpstan` — static analysis (level 8)
- `make test` — PHPUnit test suite ([tests/](tests/))
