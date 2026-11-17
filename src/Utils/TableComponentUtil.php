<?php

declare(strict_types=1);

namespace Juaniquillo\TableComponentAction\Utils;

use BackedEnum;
use Exception;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\MainBackendComponent;
use Juaniquillo\BackendComponents\Themes\DefaultThemeManager;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\BaseBackendComponent\Contracts\CompoundComponent;
use Juaniquillo\BaseBackendComponent\Contracts\ThemeManager;

/**
 * Builds the table parts that wrap the cells produced by TableRowsAction.
 *
 * The cells returned by TableRowsAction are already <td> components, so this
 * utility places them directly inside a <tr> instead of wrapping them again.
 */
final readonly class TableComponentUtil
{
    /**
     * @param  class-string<BackendComponent|CompoundComponent>  $component
     */
    public function __construct(
        private ThemeManager $themeManager = new DefaultThemeManager,
        private string $component = MainBackendComponent::class,
    ) {}

    /**
     * @param  array<string, string|array<int|string, string>>  $themes
     * @param  array<string, int|string|null>  $attributes
     */
    public function header(
        string|int|BackendComponent|CompoundComponent $header,
        string|BackedEnum $type = ComponentEnum::TH,
        array $themes = [],
        array $attributes = [],
    ): CompoundComponent {
        return $this->resolveComponent([$header], $type, $themes, $attributes);
    }

    /**
     * @param  array<string|int, string|int|BackendComponent|CompoundComponent>  $cells
     * @param  array<string, string|array<int|string, string>>  $themes
     * @param  array<string, int|string|null>  $attributes
     */
    public function row(
        array $cells,
        string|BackedEnum $type = ComponentEnum::TR,
        array $themes = [],
        array $attributes = [],
    ): CompoundComponent {
        return $this->resolveComponent($cells, $type, $themes, $attributes);
    }

    /**
     * @param  array<string|int, string|int|BackendComponent|CompoundComponent>  $headers
     * @param  array<string, string|array<int|string, string>>  $themes
     * @param  array<string, int|string|null>  $attributes
     */
    public function head(
        array $headers,
        string|BackedEnum $type = ComponentEnum::THEAD,
        array $themes = [],
        array $attributes = [],
        string|BackedEnum $rowType = ComponentEnum::TR,
    ): CompoundComponent {
        return $this->resolveComponent(
            contents: [$this->resolveComponent($headers, $rowType)],
            type: $type,
            themes: $themes,
            attributes: $attributes,
        );
    }

    /**
     * @param  array<string|int, string|int|BackendComponent|CompoundComponent>  $rows
     * @param  array<string, string|array<int|string, string>>  $themes
     * @param  array<string, int|string|null>  $attributes
     */
    public function body(
        array $rows,
        string|BackedEnum $type = ComponentEnum::TBODY,
        array $themes = [],
        array $attributes = [],
    ): CompoundComponent {
        return $this->resolveComponent($rows, $type, $themes, $attributes);
    }

    /**
     * @param  array<string|int, string|int|BackendComponent|CompoundComponent>  $contents
     * @param  array<string, string|array<int|string, string>>  $themes
     * @param  array<string, int|string|null>  $attributes
     */
    public function table(
        array $contents,
        string|BackedEnum $type = ComponentEnum::TABLE,
        array $themes = [],
        array $attributes = [],
    ): CompoundComponent {
        return $this->resolveComponent($contents, $type, $themes, $attributes);
    }

    /**
     * @param  array<string|int, string|int|BackendComponent|CompoundComponent>  $contents
     * @param  array<string, string|array<int|string, string>>  $themes
     * @param  array<string, int|string|null>  $attributes
     */
    public function resolveComponent(
        array $contents,
        string|BackedEnum $type,
        array $themes = [],
        array $attributes = [],
        ?ThemeManager $themeManager = null,
    ): CompoundComponent {
        $class = $this->component;

        $component = new $class($type, $themeManager ?? $this->themeManager);

        if (! $component instanceof CompoundComponent) {
            throw new Exception('The component must implement '.CompoundComponent::class.'.');
        }

        $component->setAttributes($attributes);
        $component->setContents($contents);

        if ($themes !== []) {
            $component->setThemes($themes);
        }

        return $component;
    }
}
