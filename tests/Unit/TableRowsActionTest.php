<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\TableComponentAction\TableRowsAction;
use Juaniquillo\TableComponentAction\TableRowsRecipe;
use Juaniquillo\TableComponentAction\Tests\Fixtures\FakeModel;
use Juaniquillo\TableComponentAction\Tests\Fixtures\Status;

it('resolves its identifier from the action', function () {
    expect((new TableRowsRecipe)->getIdentifier())->toBe(TableRowsAction::class);
});

it('falls back to the raw value', function () {
    $action = new TableRowsAction(new FakeModel);

    expect($action->resolveValue('Alice'))->toBe('Alice');
});

it('resolves backed enums to their value', function () {
    $action = new TableRowsAction(new FakeModel);

    expect($action->resolveValue(Status::Active))->toBe('active');
});

it('resolves values through the recipe closure', function () {
    $model = new FakeModel(['name' => 'Alice']);
    $action = new TableRowsAction($model);
    $recipe = new TableRowsRecipe(
        value: fn ($value, Model $model) => strtoupper($model->name),
    );

    expect($action->resolveValue($model->name, $recipe))->toBe('ALICE');
});

it('applies action attributes and themes when the recipe has none', function () {
    $action = new TableRowsAction(
        model: new FakeModel,
        themes: ['table' => 'td'],
        attributes: ['class' => 'p-2'],
    );

    $component = $action->resolveCellComponent('Alice')->toArray();

    expect($component['attributes'])->toBe(['class' => 'p-2'])
        ->and($component['theme']['themes'])->toBe(['table' => 'td']);
});

it('prefers recipe attributes and themes over action defaults', function () {
    $action = new TableRowsAction(
        model: new FakeModel,
        themes: ['table' => 'td'],
        attributes: ['class' => 'p-2'],
    );

    $recipe = new TableRowsRecipe(
        themes: ['table' => 'td-dark'],
        attributes: ['class' => 'p-4'],
    );

    $component = $action->resolveCellComponent('Alice', $recipe)->toArray();

    expect($component['attributes'])->toBe(['class' => 'p-4'])
        ->and($component['theme']['themes'])->toBe(['table' => 'td-dark']);
});

it('applies the recipe callback', function () {
    $action = new TableRowsAction(new FakeModel);

    $recipe = new TableRowsRecipe(
        callback: fn ($value, $component) => $component->setAttribute('data-value', $value),
    );

    $component = $action->resolveCellComponent('Alice', $recipe);

    expect($component->getAttributes())->toBe(['data-value' => 'Alice']);
});

it('executes the action into its output container', function () {
    $model = new FakeModel(['name' => 'Alice']);
    $action = new TableRowsAction($model);
    $input = (new DefaultInput('name'))->setRecipe(new TableRowsRecipe(label: 'Name'));

    $action->execute($input);

    $cell = $action->getOutput()->get('Name');

    expect($cell->toArray()['contents'])->toBe(['Alice']);
});

it('adds extra cells on cleanup', function () {
    $action = (new TableRowsAction(new FakeModel(['name' => 'Alice'])))
        ->setExtraCell('email', new TableRowsRecipe(value: fn () => 'alice@example.com'));

    $action->cleanup();

    $cell = $action->getOutput()->get('email');

    expect($cell->toArray()['contents'])->toBe(['alice@example.com']);
});
