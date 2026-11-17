<?php

declare(strict_types=1);

namespace Juaniquillo\TableComponentAction\Utils;

use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\BaseBackendComponent\Contracts\CompoundComponent;
use Juaniquillo\CrudAssistant\Contracts\DataContainerInterface;

/**
 * A single table row with its cells and row-level attributes.
 */
final readonly class TableRow
{
    /**
     * @param  DataContainerInterface<mixed>|array<string|int, BackendComponent|CompoundComponent>  $cells
     * @param  array<string, int|string|null>  $attributes
     */
    public function __construct(
        public DataContainerInterface|array $cells,
        public array $attributes = [],
    ) {}
}
