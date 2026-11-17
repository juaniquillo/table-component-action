# juaniquillo/table-component-action

[![CI](https://github.com/juaniquillo/table-component-action/actions/workflows/ci.yml/badge.svg)](https://github.com/juaniquillo/table-component-action/actions/workflows/ci.yml)

Simple package providing table row action/recipe components.

## Requirements

- PHP ^8.3

## Installation

```bash
composer require juaniquillo/table-component-action
```

> Dependencies used by the source (e.g. Laravel Eloquent / CrudAssistant / BackendComponents) should be installed and configured as needed.

## Usage

`TableRowsAction` produces the `<td>` cells. `TableBuilder` assembles the
complete table, using `TableComponentUtil` under the hood:

```php
use Juaniquillo\TableComponentAction\Utils\TableBuilder;
use Juaniquillo\TableComponentAction\Utils\TableRow;

$rows = $models->map(fn ($model) => new TableRow(
    cells: $crud->execute(new TableRowsAction($model)),
    attributes: ['wire:key' => 'row-'.$model->getKey()],
));

$table = TableBuilder::make(rows: $rows)
    ->tableThemes(['table' => 'table'])
    ->getComponent();

echo $table; // <table>...</table>
```

Headers are inferred from the first row's keys; pass `headers: ['Name', 'Email']`
to control the order. Missing cells render as empty `<td>`s, unless `->strict()`
is enabled. An empty row set returns `null`.

### Custom component sets

Use `TableTypes` and a configured `TableComponentUtil` for Flux, Slate, or any
other component package:

```php
use Juaniquillo\TableComponentAction\Utils\TableTypes;

$table = TableBuilder::make(
    rows: $rows,
    util: new TableComponentUtil(component: FluxBackendComponent::class),
    types: new TableTypes(
        table: FluxComponentEnum::TABLE,
        head: FluxComponentEnum::THEAD,
        body: FluxComponentEnum::TBODY,
        row: FluxComponentEnum::TR,
        header: FluxComponentEnum::TH,
        cell: FluxComponentEnum::TD,
    ),
)->getComponent();
```

### Low-level util

`TableComponentUtil` can also be used directly when you only need the parts:

```php
use Juaniquillo\TableComponentAction\Utils\TableComponentUtil;

$util = new TableComponentUtil; // or new TableComponentUtil(component: FluxBackendComponent::class)

$cells = array_values($output->toArray()); // DataContainer from a crud run using TableRowsAction
$labels = array_keys($output->toArray());

$table = $util->table([
    $util->head(array_map(
        fn (string $label) => $util->header($label),
        $labels,
    )),
    $util->body([
        $util->row($cells),
    ]),
]);

echo $table; // <table>...</table>
```

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
