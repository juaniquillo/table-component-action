<?php

declare(strict_types=1);

namespace Juaniquillo\TableComponentAction\Utils;

use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\BaseBackendComponent\Contracts\CompoundComponent;
use Juaniquillo\CrudAssistant\Contracts\DataContainerInterface;

/**
 * Creates the row component that wraps the cells produced by TableCellsAction.
 */
final class TableRow
{
    /**
     * @param  DataContainerInterface<mixed>|array<string|int, string|int|BackendComponent|CompoundComponent>  $output
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

        return $util->row(
            cells: $output instanceof DataContainerInterface ? $output->toArray() : $output,
            type: $types->row,
            themes: $themes,
            attributes: $attributes,
        );
    }
}
