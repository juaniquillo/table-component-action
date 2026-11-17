<?php

declare(strict_types=1);

namespace Juaniquillo\TableComponentAction;

use BackedEnum;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\MainBackendComponent;
use Juaniquillo\BackendComponents\Themes\DefaultThemeManager;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\BaseBackendComponent\Contracts\CompoundComponent;
use Juaniquillo\BaseBackendComponent\Contracts\ThemeManager;
use Juaniquillo\CrudAssistant\Action;
use Juaniquillo\CrudAssistant\Contracts\ActionInterface;
use Juaniquillo\CrudAssistant\Contracts\InputCollectionInterface;
use Juaniquillo\CrudAssistant\Contracts\InputInterface;
use Juaniquillo\CrudAssistant\Contracts\RecipeInterface;
use Juaniquillo\CrudAssistant\DataContainer;
use Stringable;

class TableCellsAction extends Action implements ActionInterface
{
    /**
     * @param  array<string, string|array<int|string, string>>|Closure(Model $model): array<string, string|array<int|string, string>>  $themes
     * @param  array<string, int|string|null>|Closure(Model $model): array<string, int|string|null>  $attributes
     * @param  class-string<BackendComponent|CompoundComponent>  $component
     * @param  array<string, TableCellsRecipe|RecipeInterface>  $extraCells
     */
    public function __construct(
        private Model $model,
        private readonly ThemeManager $themeManager = new DefaultThemeManager,
        private readonly array|Closure $themes = [],
        private readonly array|Closure $attributes = [],
        private readonly string $component = MainBackendComponent::class,
        private readonly string|BackedEnum $type = ComponentEnum::TD,
        private array $extraCells = [],
    ) {
        $this->output = new DataContainer;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function setModel(Model $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function setExtraCell(string $identifier, TableCellsRecipe|RecipeInterface $recipe): static
    {
        $this->extraCells[$identifier] = $recipe;

        return $this;
    }

    /**
     * @param  array<string, TableCellsRecipe|RecipeInterface>  $extraCells
     */
    public function setExtraCells(array $extraCells): static
    {
        foreach ($extraCells as $identifier => $recipe) {
            $this->setExtraCell($identifier, $recipe);
        }

        return $this;
    }

    /**
     * Ordered labels of the cells produced so far.
     *
     * @return array<int, string|int>
     */
    public function getHeaders(): array
    {
        return array_keys($this->getOutput()->toArray());
    }

    public function execute(InputCollectionInterface|InputInterface $input): void
    {
        $recipe = $input->getRecipe($this->getIdentifier());

        if (! $recipe instanceof TableCellsRecipe) {
            throw new Exception('The '.static::getIdentifier().' recipe is required.');
        }

        $name = $input->getName();

        if (! $name) {
            throw new Exception('The input name is required.');
        }

        $model = $this->getModel();
        $output = $this->getOutput();
        $label = $recipe->label ?? $input->getLabel() ?? $name;
        $value = $model->{$name} ?? null;

        $resolvedValue = $this->resolveValue($value, $recipe);

        $output->set(
            $label,
            $this->resolveCellComponent($resolvedValue, $recipe),
        );
    }

    public function resolveValue(mixed $value = null, TableCellsRecipe|RecipeInterface|null $recipe = new TableCellsRecipe): string|int|BackendComponent|CompoundComponent|null
    {
        if (! $recipe instanceof TableCellsRecipe) {
            $recipe = new TableCellsRecipe;
        }

        $recipeValue = $recipe->value;

        if ($recipeValue instanceof Closure) {
            return $this->normalizeValue($recipeValue($value, $this->model));
        }

        if ($recipeValue !== null) {
            return $this->normalizeValue($recipeValue);
        }

        return $this->normalizeValue($value);
    }

    public function resolveCellComponent(string|int|BackendComponent|CompoundComponent|null $value = null, TableCellsRecipe|RecipeInterface|null $recipe = new TableCellsRecipe): BackendComponent|CompoundComponent
    {
        if (! $recipe instanceof TableCellsRecipe) {
            $recipe = new TableCellsRecipe;
        }

        $value ??= '';

        $componentClass = $recipe->component ?? $this->component;
        $componentType = $recipe->type ?? $this->type;
        $manager = $recipe->themeManager ?? $this->themeManager;
        $themes = $this->resolveArrayClosure(($recipe->themes ?: $this->themes), $this->model);
        $attributes = $this->resolveArrayClosure(($recipe->attributes ?: $this->attributes), $this->model);

        $component = new $componentClass($componentType, $manager);

        if (! $component instanceof CompoundComponent) {
            throw new Exception('The component must implement '.CompoundComponent::class.'.');
        }

        $component->setAttributes($attributes);
        $component->setContent($value);

        if ($themes !== []) {
            $component->setThemes($themes);
        }

        $callback = $recipe->callback;

        if ($callback instanceof Closure) {
            return $callback($value, $component);
        }

        return $component;
    }

    /**
     * @template TValue
     *
     * @param  array<string, TValue>|Closure(Model $model): array<string, TValue>  $resolve
     * @return array<string, TValue>
     */
    public function resolveArrayClosure(array|Closure $resolve, Model $model): array
    {
        if ($resolve instanceof Closure) {
            return $resolve($model);
        }

        return $resolve;
    }

    public function cleanup(): static
    {
        foreach ($this->extraCells as $name => $recipe) {
            $value = $this->resolveValue($name, $recipe);
            $component = $this->resolveCellComponent($value, $recipe);

            $this->output->set($name, $component);
        }

        return $this;
    }

    private function normalizeValue(mixed $value): string|int|BackendComponent|CompoundComponent|null
    {
        if ($value === null || is_string($value) || is_int($value) || $value instanceof BackendComponent) {
            return $value;
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof Stringable) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_float($value)) {
            return (string) $value;
        }

        throw new Exception('Unsupported table cell value of type '.get_debug_type($value).'.');
    }
}
