<?php

declare(strict_types=1);

use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\TableComponentAction\TableCellsAction;
use Juaniquillo\TableComponentAction\TableCellsRecipe;
use Juaniquillo\TableComponentAction\Tests\Fixtures\FakeModel;
use Juaniquillo\TableComponentAction\Utils\TableComponentUtil;

it('renders cells produced by the table cells action inside a table', function () {
    $action = new TableCellsAction(new FakeModel(['name' => 'Alice']));
    $action->execute(
        (new DefaultInput('name'))->setRecipe(new TableCellsRecipe(label: 'Name')),
    );

    $cells = array_values($action->getOutput()->toArray());

    $util = new TableComponentUtil;

    $table = $util->table([
        $util->head([$util->header('Name')]),
        $util->body([$util->row($cells)]),
    ]);

    $html = $table->toHtml();

    expect($html)->toContain('<table')
        ->and($html)->toContain('<thead')
        ->and($html)->toContain('<tbody')
        ->and($html)->toContain('<th')
        ->and($html)->toContain('Name')
        ->and($html)->toContain('<td')
        ->and($html)->toContain('Alice');
});
