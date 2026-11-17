<?php

declare(strict_types=1);

namespace Juaniquillo\TableComponentAction\Utils;

use Exception;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\BaseBackendComponent\Contracts\CompoundComponent;
use Juaniquillo\CrudAssistant\Contracts\DataContainerInterface;

/**
 * Creates the row component that wraps the cells produced by TableCellsAction.
 */
final class TableRow
{
    /**
     * @param  DataContainerInterface<mixed>|array<string|int, mixed>  $output
     * @param  array<string, int|string|null>  $attributes
     * @param  array<string, string|array<int|string, string>>  $themes
     */
    public static function make(
        DataContainerInterface|array $output,
        array $attributes = [],
        array $themes = [],
        ?TableComponentUtil $util = null,
        ?TableTypes $types = null,
    ): CompoundComponent {
        $util ??= new TableComponentUtil;
        $types ??= new TableTypes;

        $cells = $output instanceof DataContainerInterface ? $output->toArray() : $output;

        return $util->row(
            cells: self::normalizeCells($cells),
            type: $types->row,
            themes: $themes,
            attributes: $attributes,
        );
    }

    /**
     * @param  array<string|int, mixed>  $cells
     * @return array<string|int, string|int|BackendComponent|CompoundComponent>
     */
    private static function normalizeCells(array $cells): array
    {
        $normalized = [];

        foreach ($cells as $key => $cell) {
            if (! is_string($cell) && ! is_int($cell) && ! $cell instanceof BackendComponent) {
                throw new Exception('Unsupported table cell value of type '.get_debug_type($cell).'.');
            }

            $normalized[$key] = $cell;
        }

        return $normalized;
    }
}
