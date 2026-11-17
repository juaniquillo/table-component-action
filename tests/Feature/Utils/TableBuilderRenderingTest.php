<?php

declare(strict_types=1);

use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\TableComponentAction\TableCellsAction;
use Juaniquillo\TableComponentAction\TableCellsRecipe;
use Juaniquillo\TableComponentAction\Tests\Fixtures\FakeModel;
use Juaniquillo\TableComponentAction\Utils\TableBuilder;
use Juaniquillo\TableComponentAction\Utils\TableRow;

it('renders multiple prebuilt rows', function () {
    $rows = [];
    $headers = null;

    foreach (['Alice', 'Bob'] as $index => $name) {
        $action = new TableCellsAction(new FakeModel(['name' => $name]));
        $action->execute(
            (new DefaultInput('name'))->setRecipe(new TableCellsRecipe(label: 'Name')),
        );

        $headers ??= $action->getHeaders();

        $rows[] = TableRow::make(
            output: $action->getOutput(),
            attributes: ['wire:key' => 'row-'.$index],
        );
    }

    $table = TableBuilder::make(rows: $rows, headers: $headers)->getComponent();

    $html = $table->toHtml();

    expect($html)->toContain('<table')
        ->and($html)->toContain('<thead')
        ->and($html)->toContain('<tbody')
        ->and($html)->toContain('<th')
        ->and($html)->toContain('Name')
        ->and($html)->toContain('Alice')
        ->and($html)->toContain('Bob')
        ->and($html)->toContain('wire:key="row-0"')
        ->and($html)->toContain('wire:key="row-1"');
});
