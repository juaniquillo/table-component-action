# AGENTS.md - Table Component Action

Laravel package that provides table cell actions/recipes and utilities to
assemble the surrounding table components.

## Quick commands

| Command | Purpose |
|---|---|
| `composer qa` | Analyse + Rector check + Pint check + tests (run before every PR) |
| `composer test` | Run Pest |
| `composer test:coverage` | Pest with coverage (needs Xdebug or PCOV) |
| `composer analyse` | PHPStan/Larastan, level 10 |
| `composer rector` | Apply Rector refactors |
| `composer rector:check` | Rector dry-run |
| `composer lint` | Format `src` with Pint |
| `composer lint:check` | Pint dry-run |

CI runs on pull requests only, never on pushes to `main`. Required checks are
`QA (PHP 8.4)` and `QA (PHP 8.5)`.

## Requirements

- Runtime: PHP `^8.3`
- Development: PHP 8.4+ because Pest 5 requires it
- Laravel 11, 12, or 13 via `illuminate/*`

## Layout

```
src/
  TableCellsAction.php             # produces the <td> cells
  TableCellsRecipe.php              # per-cell configuration
  TableComponentActionServiceProvider.php
  Utils/
    TableComponentUtil.php          # stateless factory for table parts
    TableBuilder.php                # headers, validation, table output
    TableRow.php                    # creates the <tr> row component
    TableTypes.php                  # component enum set (Flux, Slate, ...)
tests/
  Unit/                             # structure and value behavior
  Feature/                          # Blade rendering via Testbench
  Fixtures/                         # FakeModel, enums, cell helpers
resources/
  boost/skills/table-component-action/SKILL.md   # Laravel Boost skill
```

Namespaces follow PSR-4 from `composer.json`:

- `Juaniquillo\TableComponentAction\` -> `src/`
- `Juaniquillo\TableComponentAction\Tests\` -> `tests/`

## Component rules

- `TableCellsAction` returns `<td>` components. Never pass those cells to the
  vendor `TableUtil`, it would wrap them in another `<td>`. Use
  `TableComponentUtil::row()` instead.
- `TableRow::make()` creates the `<tr>` component. It takes the action output,
  row attributes, row themes, and an optional `TableComponentUtil`/`TableTypes`.
- Scalar, enum, and `Stringable` cell values are normalized. Floats and
  booleans become strings; arrays and unsupported objects throw.
- Build table parts through `TableComponentUtil`. Do not instantiate
  `MainBackendComponent` directly in new code outside a fixture.
- Use `TableBuilder` with prebuilt row components and explicit headers. It does
  not infer or reorder cells. A cell/header count mismatch throws.
- Use `TableTypes` plus a configured `TableComponentUtil` for Flux, Slate, or
  any other component package. Do not hard-code vendor enums in the builder.
- An empty row set returns `null` from the builder.
- Keep the builder free of Eloquent/CRUD knowledge. Callers wrap each model's
  cells with `TableRow::make()`.

## Code conventions

- `declare(strict_types=1);` in every PHP file under `src/` and `tests/`.
- Use `readonly` classes and properties when state is immutable after
  construction. Do not make mutable fluent builders readonly.
- Fluent setters return `static`.
- Type every array shape and generic (`array<string, int|string|null>`,
  `DataContainerInterface<mixed>`, and so on). PHPStan level 10 must pass.
- Never silence PHPStan with `@phpstan-ignore`, baselines, `assert()`, or
  inline `@var` overrides. Fix the underlying type or add a runtime guard.
- Throw `Exception` with a clear message for invalid state, for example a
  component that does not implement `CompoundComponent`.
- Keep docblocks accurate. Update them when changing a signature.

## Testing

- Use Pest. Feature tests use `tests/TestCase.php` (Orchestra Testbench) and
  register `TableComponentActionServiceProvider` plus
  `BackendComponentsServiceProvider`.
- Put structural tests in `tests/Unit`, and rendering tests in
  `tests/Feature`.
- Reuse fixtures under `tests/Fixtures` instead of defining global helpers.
- Add a regression test for every bug fix. The `TableBuilder` and
  `TableComponentUtil` suites are the reference patterns.

## Composer

- No `composer.lock` is committed. CI resolves dependencies with
  `composer update`.
- Keep `require` limited to runtime needs and put tooling in `require-dev`.
- `minimum-stability` is `dev` with `prefer-stable` enabled. Prefer stable
  constraints in `require` and `require-dev`.
