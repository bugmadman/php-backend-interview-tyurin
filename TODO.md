# TODO / open questions (code review, Part 3: Discount + Pricing preview)

## 0. Test framework — replaced Nette Tester with PHPUnit

[composer.json](composer.json), [phpunit.xml.dist](phpunit.xml.dist), [tests/](tests/)

`composer.json` originally already declared the `"test": "vendor/bin/tester -C tests"` runner (Nette
Tester) — part of the original task skeleton, the package itself was never installed in `vendor`. I
started writing tests against it, but `Tester\TestCase` reserves the `@throws` docblock tag for its own
DSL ("this test method must throw the given exception" — see the package's own source,
`Tester\TestCase::runTest()`; there's no such file in this project, the package never made it into
`vendor`), while in this project `@throws` is also the mandatory PHPStan annotation for checked
exceptions (`App\Shared\Domain\Exception\RuntimeException`, see [phpstan.dist.neon](phpstan.dist.neon)).
The two meanings of the tag collide on the exact same spot: add `@throws` for PHPStan, and Nette Tester
starts requiring the test to actually throw, breaking happy-path tests where no exception is expected. I
didn't want to work around this with PHPStan's `ignoreErrors` — instead I swapped the runner for
**PHPUnit** (as I've done on previous projects with a similar stack): `expectException()` isn't tied to
PHPDoc, so there's no conflict, and `@throws` works as a plain annotation again. Swapped the dependency
(`nette/tester` → `phpunit/phpunit`), the `composer.json`/`Makefile` test scripts, and added
`phpunit.xml.dist`.

Haven't wired up mocking (Prophecy/PHPUnit's mock builder) yet — all current tests cover pure domain logic
(`Order`, `DiscountCatalog`, `Money`, the Id value objects, `EmailAddress`, `Inventory`) without touching
Doctrine/Slim. The handlers (`CreateOrderHandler`, `ConfirmOrderHandler`, `ApplyDiscountHandler`) aren't
covered yet — they'd need mocks for repositories/`ManagerRegistry`/`ServerRequestInterface`, which is
closer to integration testing and outside the current scope.

The uncommitted changes (`ApplyDiscount`, `Order::applyDiscount`, `DiscountCatalog`) are implemented
correctly and match the spec (`docs/Tyurin - PHP Backend Case Study.pdf`). What follows below isn't bugs,
just points for discussion / potential improvements outside the current scope.

## 1. `discount.value` in the response — string or number?

[PricingPreviewResponse.php](src/Presentation/API/Order/ApplyDiscount/PricingPreviewResponse.php),
[DiscountResponse.php](src/Presentation/API/Order/ApplyDiscount/DiscountResponse.php)

Right now `value`/`subtotal`/`discount_total`/`total` are serialized as strings (`"10.00"`), while the spec's
example response has them as numbers (`10.00`). This follows the project's existing convention (see
`totalAmount` in `ConfirmOrderResponse`), so it's not a bug. The question is what matters more: consistency
with the rest of the API's style, or a literal match with the PDF's example.

## 2. `DiscountCatalog::DEFINITIONS` — hardcoded instead of a table

[DiscountCatalog.php:11](src/Order/Domain/Discount/DiscountCatalog.php:11)

The spec explicitly asks for a code-level hardcode with no `discount_codes` table, to avoid spending time
on migrations within the 60-minute live-coding case — the current implementation is correct here. In a
real production system this would make more sense in the DB, though: discount codes are set up by
marketing independently of deploys, and need expiry dates, usage limits, customer-segment targeting, etc.
Once migrations exist for this, move `DEFINITIONS` into a `discount_codes` table + a Doctrine repository
instead of a static array.

## 3. Exception messages and i18n

[OrderDiscountCannotBeApplied.php](src/Order/Domain/Exception/OrderDiscountCannotBeApplied.php),
[DiscountAlreadyApplied.php](src/Order/Domain/Exception/DiscountAlreadyApplied.php)

Checked: `$exception->getMessage()` never ends up in the HTTP response sent to the client — not in
`ApplyDiscountHandler`/`ConfirmOrderHandler` (only the machine-readable `error.code` goes out there), nor in
`ErrorMiddleware` for uncaught exceptions (the text only goes to the log, the client gets a bare
`INTERNAL_SERVER_ERROR`). So there's no point translating the exception messages themselves right now —
they're purely internal/for logs, and client-side translation should be built off `error.code`, which is
already language-agnostic.

Needs doing:
- pin down this architectural rule explicitly (e.g. in CLAUDE.md): exception message is for
  logs/stack traces only, only `error.code` ever goes out to clients;
- audit the whole `Presentation` layer for spots where `getMessage()` accidentally leaks into the
  `errors` array of `ErrorResponse` (so far I've only checked that it's always `[]` for
  `apply-discount`/`confirm`, haven't gone through the rest of the handlers).
