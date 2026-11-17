<?php

declare(strict_types=1);

use Juaniquillo\TableComponentAction\TableCellsAction;
use Juaniquillo\TableComponentAction\TableCellsRecipe;
use Juaniquillo\TableComponentAction\Tests\Fixtures\Status;

it('uses the action class as its identifier', function () {
    expect((new TableCellsRecipe)->getIdentifier())->toBe(TableCellsAction::class);
});

it('keeps the configured label and value', function () {
    $recipe = new TableCellsRecipe(label: 'Status', value: Status::Active->value);

    expect($recipe->label)->toBe('Status')
        ->and($recipe->value)->toBe('active');
});
