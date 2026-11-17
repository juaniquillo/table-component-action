<?php

declare(strict_types=1);

namespace Juaniquillo\TableComponentAction;

use BackedEnum;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\BaseBackendComponent\Contracts\CompoundComponent;
use Juaniquillo\BaseBackendComponent\Contracts\ThemeManager;
use Juaniquillo\CrudAssistant\Concerns\IsRecipe;
use Juaniquillo\CrudAssistant\Contracts\RecipeInterface;
use Stringable;

class TableCellsRecipe implements RecipeInterface
{
    use IsRecipe;

    /**
     * @param  string|Closure(Stringable|BackedEnum|string|array<mixed>|null $value, Model $model):(string|int|float|bool|Stringable|BackendComponent|CompoundComponent)|null  $value
     * @param  array<string, string|array<int|string, string>>|Closure(Model $model): array<string, string|array<int|string, string>>  $themes
     * @param  array<string, int|string|null>|Closure(Model $model): array<string, int|string|null>  $attributes
     * @param  class-string<BackendComponent|CompoundComponent>|null  $component
     * @param  Closure(string|int|BackendComponent|CompoundComponent|null $value, BackendComponent|CompoundComponent $component): (BackendComponent|CompoundComponent)  $callback
     */
    public function __construct(
        public readonly ?string $label = null,
        public readonly string|Closure|null $value = null,
        public readonly ?ThemeManager $themeManager = null,
        public readonly array|Closure $themes = [],
        public readonly array|Closure $attributes = [],
        public readonly ?string $component = null,
        public readonly string|BackedEnum|null $type = null,
        public readonly ?Closure $callback = null,
    ) {
        $this->action = TableCellsAction::class;
    }
}
