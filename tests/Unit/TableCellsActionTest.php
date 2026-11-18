<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Juaniquillo\CrudAssistant\Inputs\DefaultInput;
use Juaniquillo\TableComponentAction\TableCellsAction;
use Juaniquillo\TableComponentAction\TableCellsRecipe;
use Juaniquillo\TableComponentAction\Tests\Fixtures\FakeModel;
use Juaniquillo\TableComponentAction\Tests\Fixtures\FluxLikeComponent;
use Juaniquillo\TableComponentAction\Tests\Fixtures\Status;

it('resolves its identifier from the action', function () {
    expect((new TableCellsRecipe)->getIdentifier())->toBe(TableCellsAction::class);
});

it('falls back to the raw value', function () {
    $action = new TableCellsAction(new FakeModel);

    expect($action->resolveValue('Alice'))->toBe('Alice');
});

it('resolves backed enums to their value', function () {
    $action = new TableCellsAction(new FakeModel);

    expect($action->resolveValue(Status::Active))->toBe('active');
});

it('resolves values through the recipe closure', function () {
    $model = new FakeModel(['name' => 'Alice']);
    $action = new TableCellsAction($model);
    $recipe = new TableCellsRecipe(
        value: fn ($value, Model $model) => strtoupper($model->name),
    );

    expect($action->resolveValue($model->name, $recipe))->toBe('ALICE');
});

it('applies action attributes and themes when the recipe has none', function () {
    $action = new TableCellsAction(
        model: new FakeModel,
        themes: ['table' => 'td'],
        attributes: ['class' => 'p-2'],
    );

    $component = $action->resolveCellComponent('Alice')->toArray();

    expect($component['attributes'])->toBe(['class' => 'p-2'])
        ->and($component['theme']['themes'])->toBe(['table' => 'td']);
});

it('prefers recipe attributes and themes over action defaults', function () {
    $action = new TableCellsAction(
        model: new FakeModel,
        themes: ['table' => 'td'],
        attributes: ['class' => 'p-2'],
    );

    $recipe = new TableCellsRecipe(
        themes: ['table' => 'td-dark'],
        attributes: ['class' => 'p-4'],
    );

    $component = $action->resolveCellComponent('Alice', $recipe)->toArray();

    expect($component['attributes'])->toBe(['class' => 'p-4'])
        ->and($component['theme']['themes'])->toBe(['table' => 'td-dark']);
});

it('applies the recipe callback', function () {
    $action = new TableCellsAction(new FakeModel);

    $recipe = new TableCellsRecipe(
        callback: fn ($value, $component) => $component->setAttribute('data-value', $value),
    );

    $component = $action->resolveCellComponent('Alice', $recipe);

    expect($component->getAttributes())->toBe(['data-value' => 'Alice']);
});

it('executes the action into its output container', function () {
    $model = new FakeModel(['name' => 'Alice']);
    $action = new TableCellsAction($model);
    $input = (new DefaultInput('name'))->setRecipe(new TableCellsRecipe(label: 'Name'));

    $action->execute($input);

    $cell = $action->getOutput()->get('Name');

    expect($cell->toArray()['contents'])->toBe(['Alice']);
});

it('exposes the ordered headers', function () {
    $action = new TableCellsAction(new FakeModel([
        'name' => 'Alice',
        'email' => 'alice@example.com',
    ]));

    $action->execute((new DefaultInput('name'))->setRecipe(new TableCellsRecipe(label: 'Name')));
    $action->execute((new DefaultInput('email'))->setRecipe(new TableCellsRecipe(label: 'Email')));

    expect($action->getHeaders())->toBe(['Name', 'Email']);
});

it('adds extra cells on cleanup', function () {
    $action = (new TableCellsAction(new FakeModel(['name' => 'Alice'])))
        ->setExtraCell('email', new TableCellsRecipe(value: fn () => 'alice@example.com'));

    $action->cleanup();

    $cell = $action->getOutput()->get('email');

    expect($cell->toArray()['contents'])->toBe(['alice@example.com']);
});

it('includes extra cells in the ordered headers after cleanup', function () {
    $action = new TableCellsAction(new FakeModel(['name' => 'Alice']));
    $action->execute((new DefaultInput('name'))->setRecipe(new TableCellsRecipe(label: 'Name')));
    $action->setExtraCell('Email', new TableCellsRecipe(value: fn () => 'alice@example.com'));

    $action->cleanup();

    expect($action->getHeaders())->toBe(['Name', 'Email']);
});

it('does not run cleanup when executing a single input', function () {
    $action = (new TableCellsAction(new FakeModel(['name' => 'Alice'])))
        ->setExtraCell('Email', new TableCellsRecipe(value: fn () => 'alice@example.com'));

    $action->execute((new DefaultInput('name'))->setRecipe(new TableCellsRecipe(label: 'Name')));

    expect($action->getHeaders())->toBe(['Name'])
        ->and($action->getOutput()->contains(['Email']))->toBeFalse();
});

it('keeps a zero string recipe value', function () {
    $action = new TableCellsAction(new FakeModel(['name' => 'Alice']));

    expect($action->resolveValue('Alice', new TableCellsRecipe(value: '0')))->toBe('0');
});

it('normalizes scalar and stringable values', function () {
    $action = new TableCellsAction(new FakeModel);

    $stringable = new class implements Stringable
    {
        public function __toString(): string
        {
            return '2026-01-01';
        }
    };

    expect($action->resolveValue(1.5))->toBe('1.5')
        ->and($action->resolveValue(true))->toBe('1')
        ->and($action->resolveValue(false))->toBe('0')
        ->and($action->resolveValue($stringable))->toBe('2026-01-01');
});

it('throws for unsupported cell values', function () {
    $action = new TableCellsAction(new FakeModel);

    expect(fn () => $action->resolveValue(['unsupported']))
        ->toThrow(Exception::class, 'Unsupported table cell value of type array.');
});

it('throws when the action recipe is missing', function () {
    $action = new TableCellsAction(new FakeModel);

    expect(fn () => $action->execute(new DefaultInput('name')))
        ->toThrow(Exception::class);
});

it('resolves cells from a non-compound content component', function () {
    $action = new TableCellsAction(
        model: new FakeModel,
        component: FluxLikeComponent::class,
    );

    $component = $action->resolveCellComponent(
        'Alice',
        new TableCellsRecipe(themes: ['table' => 'td']),
    );

    expect($component)->toBeInstanceOf(FluxLikeComponent::class)
        ->and($component->getContents())->toBe(['Alice'])
        ->and($component->getThemes())->toBe(['table' => 'td']);
});
