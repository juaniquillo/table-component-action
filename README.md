# juaniquillo/table-component-action

[![CI](https://github.com/juaniquillo/table-component-action/actions/workflows/ci.yml/badge.svg)](https://github.com/juaniquillo/table-component-action/actions/workflows/ci.yml)

Simple package providing table cell action/recipe components and table builders.

> [!TIP]
> #### Laravel Boost Skill
> If you use [Laravel Boost](https://github.com/aleksipopovicdev/laravel-boost), install the AI skill:
> ```bash
> php artisan boost:add-skill https://github.com/juaniquillo/table-component-action
> ```

## Requirements

- PHP ^8.3
- PHP 8.4+ for development (Pest 5 requires it)

## Installation

```bash
composer require juaniquillo/table-component-action
```

## Usage

`TableCellsAction` produces the `<td>` cells. `TableRow::make()` wraps them in
a `<tr>`, and `TableBuilder` assembles the table.

### Basic table

```php
use Juaniquillo\TableComponentAction\TableCellsAction;
use Juaniquillo\TableComponentAction\Utils\TableBuilder;
use Juaniquillo\TableComponentAction\Utils\TableRow;

$headers = null;
$rows = [];

foreach ($models as $key => $model) {
    $action = new TableCellsAction(model: $model);
    $output = $crud->execute($action)->toArray();

    $headers ??= $action->getHeaders();

    $rows[] = TableRow::make(
        output: $output,
        attributes: ['wire:key' => 'row-'.$key],
    );
}

$table = TableBuilder::make(rows: $rows, headers: $headers)
    ->tableThemes(['table' => 'table'])
    ->getComponent();
```

```blade
{{ $table }}
```

Cell order comes from the CRUD inputs, and row order comes from the collection.
Headers are required for prebuilt rows and only describe the `<thead>`. A row
whose cell count does not match the headers throws an exception. An empty row
set returns `null`.

### Actions column

Extra cells are added during `cleanup()`, so run the action through a CRUD
collection. `getHeaders()` includes them after cleanup.

```php
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\Factories\ComponentFactory;
use Juaniquillo\TableComponentAction\TableCellsRecipe;

// Inside the loop, before $crud->execute($action):
$action->setExtraCell('Actions', new TableCellsRecipe(
    value: fn ($value, $model) => ComponentFactory::make(ComponentEnum::BUTTON)
        ->setContent('Edit')
        ->setTheme('action', 'link')
        ->setAttribute('wire:click', 'edit('.$model->getKey().')'),
));
```

### Custom cells

Use the recipe `value` closure for custom rendering and `callback` to touch the
finished component:

```php
new TableCellsRecipe(
    label: 'Status',
    value: fn ($value, $model) => $model->status->label(),
    callback: fn ($value, $component) => $component->setAttribute('data-status', $value),
    attributes: ['class' => 'text-center'],
    themes: ['table' => ['td', 'td-dark']],
);
```

Scalar, enum, and `Stringable` values are normalized to strings. Arrays and
unsupported objects throw an exception.

### Custom component sets

Use `TableTypes` and a configured `TableComponentUtil` for Flux, Slate, or any
other component package:

```php
use Juaniquillo\FluxBackendComponents\FluxBackendComponent;
use Juaniquillo\FluxBackendComponents\FluxComponentEnum;
use Juaniquillo\TableComponentAction\TableCellsAction;
use Juaniquillo\TableComponentAction\Utils\TableComponentUtil;
use Juaniquillo\TableComponentAction\Utils\TableRow;
use Juaniquillo\TableComponentAction\Utils\TableTypes;

$util = new TableComponentUtil(component: FluxBackendComponent::class);

$types = new TableTypes(
    table: FluxComponentEnum::TABLE,
    head: FluxComponentEnum::THEAD,
    body: FluxComponentEnum::TBODY,
    row: FluxComponentEnum::TR,
    header: FluxComponentEnum::TH,
    cell: FluxComponentEnum::TD,
);

$rows = $models->map(fn ($model) => TableRow::make(
    output: $crud->execute(new TableCellsAction($model)),
    util: $util,
    types: $types,
));

$table = TableBuilder::make(
    rows: $rows,
    headers: $headers,
    util: $util,
    types: $types,
)->getComponent();
```

### Low-level util

`TableComponentUtil` can also be used directly when you only need the parts:

```php
use Juaniquillo\TableComponentAction\Utils\TableComponentUtil;

$util = new TableComponentUtil; // or new TableComponentUtil(component: FluxBackendComponent::class)

$cells = $crud->execute($action); // DataContainer keyed by label
$labels = $action->getHeaders();

$table = $util->table([
    $util->head(array_map(
        fn (string $label) => $util->header($label),
        $labels,
    )),
    $util->body([
        $util->row($cells->toArray()),
    ]),
]);
```

```blade
{{ $table }}
```

## Where to look

- **Usage recipes:** this README.
- **Executable examples:** `tests/Unit/TableCellsActionTest.php`,
  `tests/Unit/Utils/TableRowTest.php`, `tests/Unit/Utils/TableBuilderTest.php`,
  and `tests/Feature/BladeTableIntegrationTest.php`.
- **Agent guidance:** `resources/boost/skills/table-component-action/SKILL.md`.
- **Release history:** [CHANGELOG.md](CHANGELOG.md).

## Testing

```bash
composer test
```

Tests use Pest and Orchestra Testbench. Coverage requires Xdebug or PCOV:

```bash
composer test:coverage
```

## Quality

```bash
composer analyse      # PHPStan/Larastan
composer rector       # apply Rector refactors
composer rector:check # Rector dry-run
composer lint         # format with Pint
composer lint:check   # Pint dry-run
composer qa           # analyse + rector:check + lint:check + test
```

## License

MIT. See [LICENSE](LICENSE).
