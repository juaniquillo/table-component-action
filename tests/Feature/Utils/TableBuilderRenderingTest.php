<?php

declare(strict_types=1);

use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\TableComponentAction\TableRowsAction;
use Juaniquillo\TableComponentAction\TableRowsRecipe;
use Juaniquillo\TableComponentAction\Tests\Fixtures\FakeModel;
use Juaniquillo\TableComponentAction\Utils\TableBuilder;
use Juaniquillo\TableComponentAction\Utils\TableRow;

it('renders multiple rows produced by the table rows action', function () {
    $rows = [];

    foreach (['Alice', 'Bob'] as $index => $name) {
        $action = new TableRowsAction(new FakeModel(['name' => $name]));
        $action->execute(
            (new DefaultInput('name'))->setRecipe(new TableRowsRecipe(label: 'Name')),
        );

        $rows[] = new TableRow(
            cells: $action->getOutput(),
            attributes: ['wire:key' => 'row-'.$index],
        );
    }

    $table = TableBuilder::make(rows: $rows)->getComponent();

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
