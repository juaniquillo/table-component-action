<?php

declare(strict_types=1);

use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\TableComponentAction\TableCellsAction;
use Juaniquillo\TableComponentAction\TableCellsRecipe;
use Juaniquillo\TableComponentAction\Tests\Fixtures\FakeModel;
use Juaniquillo\TableComponentAction\Tests\Fixtures\TableCells;
use Juaniquillo\TableComponentAction\Utils\TableBuilder;
use Juaniquillo\TableComponentAction\Utils\TableRow;

it('renders a built table through blade', function () {
    $headers = null;
    $rows = [];

    foreach (['Alice', 'Bob'] as $key => $name) {
        $action = new TableCellsAction(new FakeModel(['name' => $name]));
        $action->execute(
            (new DefaultInput('name'))->setRecipe(new TableCellsRecipe(label: 'Name')),
        );

        $headers ??= $action->getHeaders();

        $rows[] = TableRow::make(
            output: $action->getOutput(),
            attributes: ['wire:key' => 'row-'.$key],
        );
    }

    $table = TableBuilder::make(rows: $rows, headers: $headers)
        ->tableThemes(['table' => 'table'])
        ->tableAttributes(['class' => 'min-w-full'])
        ->getComponent();

    $response = $this->blade('{{ $table }}', ['table' => $table]);

    $response->assertSee('<table', false)
        ->assertSee('min-w-full', false)
        ->assertSee('<thead', false)
        ->assertSee('<tbody', false)
        ->assertSee('<th', false)
        ->assertSee('Name')
        ->assertSee('<td', false)
        ->assertSee('Alice')
        ->assertSee('Bob')
        ->assertSee('wire:key="row-0"', false)
        ->assertSee('wire:key="row-1"', false);

    $html = (string) $response;

    expect(substr_count($html, '<td'))->toBe(2)
        ->and($html)->not->toContain('<td><td');
});

it('renders the table as html instead of escaping it', function () {
    $table = TableBuilder::make(
        rows: [TableRow::make(output: ['Name' => TableCells::td('Alice')])],
        headers: ['Name'],
    )->getComponent();

    $this->blade('{{ $table }}', ['table' => $table])
        ->assertSee('<table', false)
        ->assertDontSee('&lt;table', false);
});
