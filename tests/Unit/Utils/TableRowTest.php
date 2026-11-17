<?php

declare(strict_types=1);

use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\CrudAssistant\DataContainer;
use Juaniquillo\TableComponentAction\Tests\Fixtures\TableCells;
use Juaniquillo\TableComponentAction\Tests\Fixtures\TableComponentType;
use Juaniquillo\TableComponentAction\Utils\TableRow;
use Juaniquillo\TableComponentAction\Utils\TableTypes;

it('creates a row component with the cells in output order', function () {
    $row = TableRow::make(DataContainer::make([
        'Name' => TableCells::td('Alice'),
        'Email' => TableCells::td('alice@example.com'),
    ]));

    $array = $row->toArray();

    expect($array['name'])->toBe(ComponentEnum::TR->value)
        ->and($array['contents'])->toHaveCount(2)
        ->and($array['contents'][0]['contents'])->toBe(['Alice'])
        ->and($array['contents'][1]['contents'])->toBe(['alice@example.com']);
});

it('applies attributes and themes to the row', function () {
    $row = TableRow::make(
        output: ['Name' => TableCells::td('Alice')],
        attributes: ['wire:key' => 'row-1'],
        themes: ['table' => 'tr'],
    );

    $array = $row->toArray();

    expect($array['attributes'])->toBe(['wire:key' => 'row-1'])
        ->and($array['theme']['themes'])->toBe(['table' => 'tr']);
});

it('uses the configured row type', function () {
    $types = new TableTypes(row: TableComponentType::Row);

    $row = TableRow::make(
        output: ['Name' => TableCells::td('Alice')],
        types: $types,
    );

    expect($row->toArray()['name'])->toBe('table.tr');
});

it('throws for unsupported cell values', function () {
    expect(fn () => TableRow::make(output: ['Name' => ['unsupported']]))
        ->toThrow(Exception::class, 'Unsupported table cell value of type array.');
});
