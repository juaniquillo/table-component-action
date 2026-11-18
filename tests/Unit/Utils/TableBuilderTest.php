<?php

declare(strict_types=1);

use Juaniquillo\CrudAssistant\DataContainer;
use Juaniquillo\TableComponentAction\Tests\Fixtures\FluxLikeComponent;
use Juaniquillo\TableComponentAction\Tests\Fixtures\TableCells;
use Juaniquillo\TableComponentAction\Tests\Fixtures\TableComponentType;
use Juaniquillo\TableComponentAction\Utils\TableBuilder;
use Juaniquillo\TableComponentAction\Utils\TableComponentUtil;
use Juaniquillo\TableComponentAction\Utils\TableRow;
use Juaniquillo\TableComponentAction\Utils\TableTypes;

it('builds a table from prebuilt rows and label headers', function () {
    $rows = [
        TableRow::make(DataContainer::make([
            'Name' => TableCells::td('Alice'),
            'Email' => TableCells::td('alice@example.com'),
        ])),
        TableRow::make(DataContainer::make([
            'Name' => TableCells::td('Bob'),
            'Email' => TableCells::td('bob@example.com'),
        ])),
    ];

    $components = TableBuilder::make(rows: $rows, headers: ['Name', 'Email'])->components();

    $head = $components['head']->toArray();
    $body = $components['body']->toArray();

    expect($head['contents'][0]['contents'])->toHaveCount(2)
        ->and($head['contents'][0]['contents'][0]['contents'])->toBe(['Name'])
        ->and($head['contents'][0]['contents'][1]['contents'])->toBe(['Email'])
        ->and($body['contents'])->toHaveCount(2)
        ->and($body['contents'][0]['contents'][0]['contents'])->toBe(['Alice'])
        ->and($body['contents'][0]['contents'][1]['contents'])->toBe(['alice@example.com'])
        ->and($body['contents'][1]['contents'][0]['contents'])->toBe(['Bob'])
        ->and($body['contents'][1]['contents'][1]['contents'])->toBe(['bob@example.com']);
});

it('accepts prebuilt header components', function () {
    $util = new TableComponentUtil;

    $rows = [
        TableRow::make(DataContainer::make(['Name' => TableCells::td('Alice')])),
    ];

    $components = TableBuilder::make(
        rows: $rows,
        headers: [$util->header('Name')],
    )->components();

    $head = $components['head']->toArray();

    expect($head['contents'][0]['contents'][0]['contents'])->toBe(['Name']);
});

it('throws when headers are missing for prebuilt rows', function () {
    $rows = [
        TableRow::make(DataContainer::make(['Name' => TableCells::td('Alice')])),
    ];

    expect(fn () => TableBuilder::make(rows: $rows)->components())
        ->toThrow(Exception::class, 'Headers are required when building a table from prebuilt rows.');
});

it('throws when a row cell count does not match the headers', function () {
    $rows = [
        TableRow::make(DataContainer::make(['Name' => TableCells::td('Alice')])),
    ];

    expect(fn () => TableBuilder::make(rows: $rows, headers: ['Name', 'Email'])->components())
        ->toThrow(Exception::class, 'Row "0" has 1 cells but 2 headers were provided.');
});

it('returns null when there are no rows', function () {
    $builder = TableBuilder::make(rows: []);

    expect($builder->components())->toBeNull()
        ->and($builder->getComponent())->toBeNull()
        ->and($builder->toArray())->toBeNull();
});

it('applies themes and attributes to every table part', function () {
    $components = TableBuilder::make(
        rows: [TableRow::make(DataContainer::make(['Name' => TableCells::td('Alice')]))],
        headers: ['Name'],
    )
        ->tableAttributes(['class' => 'min-w-full'])
        ->tableThemes(['table' => 'table'])
        ->headThemes(['table' => 'thead'])
        ->headerThemes(['table' => 'th'])
        ->bodyThemes(['table' => 'tbody'])
        ->components();

    $table = $components['table']->toArray();
    $head = $components['head']->toArray();
    $body = $components['body']->toArray();

    expect($table['attributes'])->toBe(['class' => 'min-w-full'])
        ->and($table['theme']['themes'])->toBe(['table' => 'table'])
        ->and($head['theme']['themes'])->toBe(['table' => 'thead'])
        ->and($head['contents'][0]['contents'][0]['theme']['themes'])->toBe(['table' => 'th'])
        ->and($body['theme']['themes'])->toBe(['table' => 'tbody']);
});

it('uses the configured table types', function () {
    $types = new TableTypes(
        table: TableComponentType::Table,
        head: TableComponentType::Head,
        body: TableComponentType::Body,
        row: TableComponentType::Row,
        header: TableComponentType::Header,
        cell: TableComponentType::Cell,
    );

    $components = TableBuilder::make(
        rows: [TableRow::make(
            output: DataContainer::make(['Name' => TableCells::td('Alice')]),
            types: $types,
        )],
        headers: ['Name'],
        types: $types,
    )->components();

    expect($components['table']->toArray()['name'])->toBe('table.table')
        ->and($components['head']->toArray()['name'])->toBe('table.thead')
        ->and($components['body']->toArray()['name'])->toBe('table.tbody')
        ->and($components['body']->toArray()['contents'][0]['name'])->toBe('table.tr')
        ->and($components['head']->toArray()['contents'][0]['contents'][0]['name'])->toBe('table.th');
});

it('does not reorder cells when headers are in another order', function () {
    $rows = [
        TableRow::make(DataContainer::make([
            'Name' => TableCells::td('Alice'),
            'Email' => TableCells::td('alice@example.com'),
        ])),
    ];

    $components = TableBuilder::make(
        rows: $rows,
        headers: ['Email', 'Name'],
    )->components();

    $head = $components['head']->toArray();
    $body = $components['body']->toArray();

    expect($head['contents'][0]['contents'][0]['contents'])->toBe(['Email'])
        ->and($head['contents'][0]['contents'][1]['contents'])->toBe(['Name'])
        ->and($body['contents'][0]['contents'][0]['contents'])->toBe(['Alice'])
        ->and($body['contents'][0]['contents'][1]['contents'])->toBe(['alice@example.com']);
});

it('throws for empty headers', function () {
    $rows = [
        TableRow::make(DataContainer::make(['Name' => TableCells::td('Alice')])),
    ];

    expect(fn () => TableBuilder::make(rows: $rows, headers: [])->components())
        ->toThrow(Exception::class, 'Headers are required when building a table from prebuilt rows.');
});

it('reports the row key on a cell count mismatch', function () {
    $rows = [
        'model-1' => TableRow::make(DataContainer::make(['Name' => TableCells::td('Alice')])),
    ];

    expect(fn () => TableBuilder::make(rows: $rows, headers: ['Name', 'Email'])->components())
        ->toThrow(Exception::class, 'Row "model-1" has 1 cells but 2 headers were provided.');
});

it('builds a table with non-compound row components', function () {
    $util = new TableComponentUtil(component: FluxLikeComponent::class);

    $rows = [
        TableRow::make(
            output: DataContainer::make(['Name' => TableCells::td('Alice')]),
            util: $util,
        ),
    ];

    $builder = TableBuilder::make(rows: $rows, headers: ['Name'], util: $util);
    $components = $builder->components();

    expect($builder->getComponent())->toBeInstanceOf(FluxLikeComponent::class)
        ->and($components['head'])->toBeInstanceOf(FluxLikeComponent::class)
        ->and($components['body'])->toBeInstanceOf(FluxLikeComponent::class)
        ->and($components['table'])->toBeInstanceOf(FluxLikeComponent::class);
});
