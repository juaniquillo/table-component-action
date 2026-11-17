<?php

declare(strict_types=1);

use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\TableComponentAction\TableRowsAction;
use Juaniquillo\TableComponentAction\TableRowsRecipe;
use Juaniquillo\TableComponentAction\Tests\Fixtures\FakeModel;
use Juaniquillo\TableComponentAction\Utils\TableComponentUtil;

it('renders cells produced by the table rows action inside a table', function () {
    $action = new TableRowsAction(new FakeModel(['name' => 'Alice']));
    $action->execute(
        (new DefaultInput('name'))->setRecipe(new TableRowsRecipe(label: 'Name')),
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
