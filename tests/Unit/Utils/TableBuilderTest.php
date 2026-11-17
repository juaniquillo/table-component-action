<?php

declare(strict_types=1);

use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\CrudAssistant\DataContainer;
use Juaniquillo\TableComponentAction\Tests\Fixtures\TableCells;
use Juaniquillo\TableComponentAction\Tests\Fixtures\TableComponentType;
use Juaniquillo\TableComponentAction\Utils\TableBuilder;
use Juaniquillo\TableComponentAction\Utils\TableRow;
use Juaniquillo\TableComponentAction\Utils\TableTypes;

it('infers headers from the first row and keeps header order', function () {
    $rows = [
        DataContainer::make([
            'Name' => TableCells::td('Alice'),
            'Email' => TableCells::td('alice@example.com'),
        ]),
        DataContainer::make([
            'Email' => TableCells::td('bob@example.com'),
            'Name' => TableCells::td('Bob'),
        ]),
    ];

    $components = TableBuilder::make(rows: $rows)->components();

    $head = $components['head']->toArray();
    $body = $components['body']->toArray();

    expect($head['contents'][0]['contents'])->toHaveCount(2)
        ->and($head['contents'][0]['contents'][0]['contents'])->toBe(['Name'])
        ->and($head['contents'][0]['contents'][1]['contents'])->toBe(['Email'])
        ->and($body['contents'][1]['contents'][0]['contents'])->toBe(['Bob'])
        ->and($body['contents'][1]['contents'][1]['contents'])->toBe(['bob@example.com']);
});

it('uses explicit headers and reorders the cells', function () {
    $rows = [
        DataContainer::make([
            'Email' => TableCells::td('alice@example.com'),
            'Name' => TableCells::td('Alice'),
        ]),
    ];

    $body = TableBuilder::make(rows: $rows, headers: ['Name', 'Email'])
        ->components()['body']->toArray();

    expect($body['contents'][0]['contents'][0]['contents'])->toBe(['Alice'])
        ->and($body['contents'][0]['contents'][1]['contents'])->toBe(['alice@example.com']);
});

it('renders an empty cell when a header is missing', function () {
    $rows = [
        DataContainer::make(['Name' => TableCells::td('Alice')]),
    ];

    $body = TableBuilder::make(rows: $rows, headers: ['Name', 'Email'])
        ->components()['body']->toArray();

    expect($body['contents'][0]['contents'])->toHaveCount(2)
        ->and($body['contents'][0]['contents'][1]['contents'])->toBe([]);
});

it('throws for missing cells in strict mode', function () {
    $rows = [
        DataContainer::make(['Name' => TableCells::td('Alice')]),
    ];

    expect(fn () => TableBuilder::make(rows: $rows, headers: ['Name', 'Email'])->strict()->components())
        ->toThrow(Exception::class, 'Missing cell for header "Email".');
});

it('wraps scalar cells in a cell component', function () {
    $body = TableBuilder::make(rows: [['Name' => 'Alice']])
        ->components()['body']->toArray();

    expect($body['contents'][0]['contents'][0]['name'])->toBe(ComponentEnum::TD->value)
        ->and($body['contents'][0]['contents'][0]['contents'])->toBe(['Alice']);
});

it('returns null when there are no rows', function () {
    $builder = TableBuilder::make(rows: []);

    expect($builder->components())->toBeNull()
        ->and($builder->getComponent())->toBeNull()
        ->and($builder->toArray())->toBeNull();
});

it('merges row attributes with the resolver', function () {
    $rows = [
        new TableRow(
            cells: ['Name' => TableCells::td('Alice')],
            attributes: ['wire:key' => 'model-1', 'data-id' => '1'],
        ),
    ];

    $body = TableBuilder::make(rows: $rows)
        ->rowAttributes(fn ($row, $index) => ['wire:key' => 'row-'.$index, 'class' => 'p-2'])
        ->components()['body']->toArray();

    expect($body['contents'][0]['attributes'])->toBe([
        'wire:key' => 'row-0',
        'data-id' => '1',
        'class' => 'p-2',
    ]);
});

it('applies themes and attributes to every table part', function () {
    $components = TableBuilder::make(rows: [DataContainer::make(['Name' => TableCells::td('Alice')])])
        ->tableAttributes(['class' => 'min-w-full'])
        ->tableThemes(['table' => 'table'])
        ->headThemes(['table' => 'thead'])
        ->headerThemes(['table' => 'th'])
        ->bodyThemes(['table' => 'tbody'])
        ->rowThemes(['table' => 'tr'])
        ->components();

    $table = $components['table']->toArray();
    $head = $components['head']->toArray();
    $body = $components['body']->toArray();

    expect($table['attributes'])->toBe(['class' => 'min-w-full'])
        ->and($table['theme']['themes'])->toBe(['table' => 'table'])
        ->and($head['theme']['themes'])->toBe(['table' => 'thead'])
        ->and($head['contents'][0]['contents'][0]['theme']['themes'])->toBe(['table' => 'th'])
        ->and($body['theme']['themes'])->toBe(['table' => 'tbody'])
        ->and($body['contents'][0]['theme']['themes'])->toBe(['table' => 'tr']);
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
        rows: [DataContainer::make(['Name' => TableCells::td('Alice')])],
        headers: ['Name', 'Email'],
        types: $types,
    )->components();

    expect($components['table']->toArray()['name'])->toBe('table.table')
        ->and($components['head']->toArray()['name'])->toBe('table.thead')
        ->and($components['body']->toArray()['name'])->toBe('table.tbody')
        ->and($components['body']->toArray()['contents'][0]['name'])->toBe('table.tr')
        ->and($components['head']->toArray()['contents'][0]['contents'][0]['name'])->toBe('table.th')
        ->and($components['body']->toArray()['contents'][0]['contents'][1]['name'])->toBe('table.td');
});
