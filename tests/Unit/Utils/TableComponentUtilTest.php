<?php

declare(strict_types=1);

use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\TableComponentAction\Tests\Fixtures\IncompleteBackendComponent;
use Juaniquillo\TableComponentAction\Tests\Fixtures\TableCells;
use Juaniquillo\TableComponentAction\Utils\TableComponentUtil;

it('builds a header cell with attributes and themes', function () {
    $util = new TableComponentUtil;

    $header = $util->header(
        header: 'Name',
        themes: ['table' => ['th', 'th-dark']],
        attributes: ['scope' => 'col'],
    );

    $array = $header->toArray();

    expect($array['name'])->toBe(ComponentEnum::TH->value)
        ->and($array['contents'])->toBe(['Name'])
        ->and($array['attributes'])->toBe(['scope' => 'col'])
        ->and($array['theme']['themes'])->toBe(['table' => ['th', 'th-dark']]);
});

it('keeps the given cell components inside a row', function () {
    $util = new TableComponentUtil;

    $first = TableCells::td('Alice');
    $second = TableCells::td('alice@example.com');

    $contents = $util->row([$first, $second])->toArray()['contents'];

    expect($contents)->toHaveCount(2)
        ->and($contents[0])->toBe($first->toArray())
        ->and($contents[1])->toBe($second->toArray());
});

it('assembles the head, body and table parts', function () {
    $util = new TableComponentUtil;

    $head = $util->head([
        $util->header('Name'),
        $util->header('Email'),
    ]);

    $body = $util->body([
        $util->row([TableCells::td('Alice'), TableCells::td('alice@example.com')]),
    ]);

    $table = $util->table([$head, $body]);

    $array = $table->toArray();

    expect($array['name'])->toBe(ComponentEnum::TABLE->value)
        ->and($array['contents'])->toHaveCount(2)
        ->and($array['contents'][0]['name'])->toBe(ComponentEnum::THEAD->value)
        ->and($array['contents'][0]['contents'][0]['name'])->toBe(ComponentEnum::TR->value)
        ->and($array['contents'][0]['contents'][0]['contents'])->toHaveCount(2)
        ->and($array['contents'][1]['name'])->toBe(ComponentEnum::TBODY->value)
        ->and($array['contents'][1]['contents'][0]['name'])->toBe(ComponentEnum::TR->value)
        ->and($array['contents'][1]['contents'][0]['contents'][0])->toBe(TableCells::td('Alice')->toArray());
});

it('applies themes and attributes to every table part', function () {
    $util = new TableComponentUtil;

    $table = $util->table(
        contents: [
            $util->head(
                headers: [$util->header('Name')],
                themes: ['table' => ['th', 'th-dark']],
            ),
            $util->body(
                rows: [$util->row([TableCells::td('Alice')], attributes: ['wire:key' => 'row-1'])],
                themes: ['table' => 'tbody'],
            ),
        ],
        themes: ['table' => 'table'],
        attributes: ['class' => 'min-w-full'],
    );

    $array = $table->toArray();

    expect($array['attributes'])->toBe(['class' => 'min-w-full'])
        ->and($array['theme']['themes'])->toBe(['table' => 'table'])
        ->and($array['contents'][0]['theme']['themes'])->toBe(['table' => ['th', 'th-dark']])
        ->and($array['contents'][1]['theme']['themes'])->toBe(['table' => 'tbody'])
        ->and($array['contents'][1]['contents'][0]['attributes'])->toBe(['wire:key' => 'row-1']);
});

it('throws when the configured component cannot hold content', function () {
    $util = new TableComponentUtil(component: IncompleteBackendComponent::class);

    expect(fn () => $util->table([]))
        ->toThrow(Exception::class, 'The component must implement');
});
