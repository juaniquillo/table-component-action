<?php

declare(strict_types=1);

use Juaniquillo\TableComponentAction\TableRowsAction;
use Juaniquillo\TableComponentAction\TableRowsRecipe;
use Juaniquillo\TableComponentAction\Tests\Fixtures\Status;

it('uses the action class as its identifier', function () {
    expect((new TableRowsRecipe)->getIdentifier())->toBe(TableRowsAction::class);
});

it('keeps the configured label and value', function () {
    $recipe = new TableRowsRecipe(label: 'Status', value: Status::Active->value);

    expect($recipe->label)->toBe('Status')
        ->and($recipe->value)->toBe('active');
});
