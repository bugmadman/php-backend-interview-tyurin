# CLAUDE.md

Instructions for Claude Code working in this repository.

## Project
Vilgain PHP backend interview task. Stack: **Slim Framework 4** (PSR-15 routing/middleware), **Nette DI** (container, `.neon` config), **Doctrine ORM/DBAL**, plus standalone **Symfony Serializer/PropertyAccess** components used only for JSON (de)serialization. The overall shape — DI container + Doctrine + declarative routing, no MVC controller classes — resembles a typical Symfony app; it isn't one, there's no `bin/console`, no `src/Controller`, no Symfony Kernel/Router. PHP 8.4, heavy use of `readonly` classes.

## Architecture — where things go
No MVC controllers. Each API endpoint is a vertical slice under `src/Presentation/API/<Feature>/<Action>/`:
- **`<Action>Handler`** — implements PSR-15 `RequestHandlerInterface` (`handle()` method). This is the "controller" for one action. See [GetHelloHandler.php](src/Presentation/API/Hello/GetHello/GetHelloHandler.php) as the template.
- **`<Feature>Router`** — implements `App\Presentation\Api\Router`, registers Slim routes in `setupRoutes(App $app)`. See [HelloRouter.php](src/Presentation/API/Hello/HelloRouter.php).
- Both are registered as plain DI services in a `config.neon` next to the feature (e.g. [Hello/config/config.neon](src/Presentation/API/Hello/config/config.neon)), which is pulled in via `includes:` from [API/config/config.neon](src/Presentation/API/config/config.neon). **New feature → add its `config.neon` to that `includes:` list, or it never loads.**
- `MainRouter` ([MainRouter.php](src/Presentation/API/MainRouter.php)) auto-collects every service tagged with the `Router` interface via Nette's `typed(App\Presentation\Api\Router)` — no manual route list to maintain.
- JSON request bodies: a Handler that implements `JsonRequestWithParsedBodyHandler` (adds a static `getParsedBodyClassName()`) gets its body auto-deserialized into that DTO class by `JsonSerializerMiddleware`, then attached via `$request->withParsedBody(...)` before `handle()` runs. Don't manually `json_decode` request bodies.
- Responses go through `JsonResponseFactory` (`create`, `createBadRequest`, `createNotFound`, ... — one method per `StatusCode` case). Errors thrown from a handler are caught centrally by `ErrorMiddleware` and turned into a uniform `ErrorResponse` JSON body.

## Database
Postgres 16 + `orders`/`order_items`/`products`/`customers`/`inventory` tables already exist — created via **Phinx** migrations (`database/Migration/`), not Doctrine Migrations. Doctrine ORM entity mapping for these tables does not exist yet and needs to be added under `App\` namespaces matching `doctrine.neon`'s `mapping` config. Custom Doctrine types are pre-registered: `uuid`, `big_decimal`, `big_integer`, `instant`, `local_date(_time)`, `unsigned_integer` (see [src/config/doctrine.neon](src/config/doctrine.neon)). IDs are `UuidId`/`ScalarId` value objects (`App\Shared\Domain\Id`), not raw strings — money uses `App\Shared\Domain\Money\Money` (`Brick\Math\BigDecimal` + `CurrencyIsoCode`), not floats.

## Coding standards (enforced by `phpcs`, `ruleset.xml`)
1. `declare(strict_types=1);` on the first line of every file, one blank/space, no leading blank line.
2. Class body order: `use` traits → enum cases → constants (public/protected/private) → static properties (public/protected/private) → properties (public/protected/private) → constructor → methods.
3. `use` imports: alphabetically sorted, never start with a leading `\`.
4. Nullable types written as `Type|null` (not `?Type`), `null` last in unions.
5. No `Type[]` array type hints — use PHPDoc generics (`array<int, Foo>` etc.); exceptions only for `\Traversable`, `\Generator`, `\Iterator`, `Doctrine\Common\Collections\Collection`.
6. Trailing commas required in multi-line calls and declarations.
7. Prefer arrow functions (`SlevomatCodingStandard.Functions.RequireArrowFunction`).
8. `var_dump`, `dump`, `bdump` are forbidden — use the injected `LoggerInterface`/Monolog instead.
9. Single quotes unless the string actually interpolates.

## Static analysis
PHPStan **level 8** (`phpstan-doctrine`, `phpstan-strict-rules`, `shipmonk/phpstan-rules`, bleeding edge) — see [phpstan.dist.neon](phpstan.dist.neon). Notable: `App\Shared\Domain\Exception\RuntimeException` is configured as a **checked exception** (must appear in `@throws`, "too wide throw type" is flagged); `LogicException` is unchecked (programmer error, don't `@throws`-annotate it defensively).

## Running things
Everything runs inside the `app` Docker container (`docker-compose`). Use the `Makefile` targets, don't call `composer`/`vendor/bin/*` on the host:
- `make reset` — full rebuild: containers up, `composer install`, wait for db, migrate, seed
- `make db-migrate` / `make db-rollback` / `make db-seed` / `make db-create args="Name"` — Phinx
- `make phpcs` / `make phpcs-fix` — coding standard check/fix (`args=` to scope a path)
- `make phpstan` — static analysis
- Dev server runs on `php -S 0.0.0.0:8000 -t public` (see `composer.json` `dev` script / Dockerfile `CMD`) → http://localhost:8000

## Tests
`composer.json` wires `vendor/bin/tester -C tests` (**Nette Tester**, not PHPUnit) as the `test` script, but no `tests/` directory exists yet — create it (autoloaded as `Tests\` per `composer.json`) when adding tests.
