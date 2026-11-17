<?php

declare(strict_types=1);

use Juaniquillo\TableComponentAction\TableCellsAction;
use Juaniquillo\TableComponentAction\Tests\Fixtures\FakeModel;

it('renders a table cell', function () {
    $action = new TableCellsAction(new FakeModel);

    $html = $action->resolveCellComponent('Alice')->toHtml();

    expect($html)->toContain('<td')
        ->and($html)->toContain('Alice');
});

it('renders the configured theme classes', function () {
    $action = new TableCellsAction(
        model: new FakeModel,
        themes: ['table' => 'td'],
    );

    $html = $action->resolveCellComponent('Alice')->toHtml();

    expect($html)->toContain('px-3 py-3 border-b border-zinc-300 bg-white align-middle');
});
