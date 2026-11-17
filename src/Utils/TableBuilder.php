<?php

declare(strict_types=1);

namespace Juaniquillo\TableComponentAction\Utils;

use Closure;
use Exception;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\BaseBackendComponent\Contracts\CompoundComponent;
use Juaniquillo\CrudAssistant\Contracts\DataContainerInterface;

/**
 * Assembles a complete table from the cells produced by TableRowsAction.
 *
 * The builder only orchestrates headers, ordering and row attributes. All
 * component creation is delegated to TableComponentUtil.
 */
final class TableBuilder
{
    /**
     * @var array<int, TableRow|DataContainerInterface<mixed>|array<string|int, mixed>>
     */
    private readonly array $rows;

    /**
     * @var array<int, string|int>|null
     */
    private readonly ?array $headers;

    private readonly TableComponentUtil $util;

    private readonly TableTypes $types;

    /**
     * @var array<string, int|string|null>
     */
    private array $tableAttributes = [];

    /**
     * @var array<string, string|array<int|string, string>>
     */
    private array $tableThemes = [];

    /**
     * @var array<string, string|array<int|string, string>>
     */
    private array $headThemes = [];

    /**
     * @var array<string, string|array<int|string, string>>
     */
    private array $headerThemes = [];

    /**
     * @var array<string, string|array<int|string, string>>
     */
    private array $bodyThemes = [];

    /**
     * @var array<string, string|array<int|string, string>>
     */
    private array $rowThemes = [];

    /**
     * @var (Closure(TableRow|DataContainerInterface<mixed>|array<string|int, mixed>, int): array<string, int|string|null>)|null
     */
    private ?Closure $rowAttributes = null;

    private bool $strict = false;

    /**
     * @param  iterable<TableRow|DataContainerInterface<mixed>|array<string|int, mixed>>  $rows
     * @param  array<int, string|int>|null  $headers
     */
    public function __construct(
        iterable $rows,
        ?array $headers = null,
        ?TableComponentUtil $util = null,
        ?TableTypes $types = null,
    ) {
        $this->rows = is_array($rows) ? array_values($rows) : iterator_to_array($rows, false);
        $this->headers = $headers === null ? null : array_values($headers);
        $this->util = $util ?? new TableComponentUtil;
        $this->types = $types ?? new TableTypes;
    }

    /**
     * @param  iterable<TableRow|DataContainerInterface<mixed>|array<string|int, mixed>>  $rows
     * @param  array<int, string|int>|null  $headers
     */
    public static function make(
        iterable $rows,
        ?array $headers = null,
        ?TableComponentUtil $util = null,
        ?TableTypes $types = null,
    ): static {
        return new self($rows, $headers, $util, $types);
    }

    /**
     * @param  array<string, int|string|null>  $attributes
     */
    public function tableAttributes(array $attributes): static
    {
        $this->tableAttributes = $attributes;

        return $this;
    }

    /**
     * @param  array<string, string|array<int|string, string>>  $themes
     */
    public function tableThemes(array $themes): static
    {
        $this->tableThemes = $themes;

        return $this;
    }

    /**
     * @param  array<string, string|array<int|string, string>>  $themes
     */
    public function headThemes(array $themes): static
    {
        $this->headThemes = $themes;

        return $this;
    }

    /**
     * @param  array<string, string|array<int|string, string>>  $themes
     */
    public function headerThemes(array $themes): static
    {
        $this->headerThemes = $themes;

        return $this;
    }

    /**
     * @param  array<string, string|array<int|string, string>>  $themes
     */
    public function bodyThemes(array $themes): static
    {
        $this->bodyThemes = $themes;

        return $this;
    }

    /**
     * @param  array<string, string|array<int|string, string>>  $themes
     */
    public function rowThemes(array $themes): static
    {
        $this->rowThemes = $themes;

        return $this;
    }

    /**
     * Resolver returning extra attributes for each row. Resolver values win
     * over the attributes set on TableRow.
     *
     * @param  Closure(TableRow|DataContainerInterface<mixed>|array<string|int, mixed>, int): array<string, int|string|null>  $resolver
     */
    public function rowAttributes(Closure $resolver): static
    {
        $this->rowAttributes = $resolver;

        return $this;
    }

    public function strict(bool $strict = true): static
    {
        $this->strict = $strict;

        return $this;
    }

    /**
     * @return array{head: CompoundComponent, body: CompoundComponent, table: CompoundComponent}|null
     */
    public function components(): ?array
    {
        if ($this->rows === []) {
            return null;
        }

        $headers = $this->resolveHeaders();

        $head = $this->util->head(
            headers: $this->buildHeaders($headers),
            type: $this->types->head,
            themes: $this->headThemes,
            rowType: $this->types->row,
        );

        $body = $this->util->body(
            rows: $this->buildRows($headers),
            type: $this->types->body,
            themes: $this->bodyThemes,
        );

        $table = $this->util->table(
            contents: [$head, $body],
            type: $this->types->table,
            themes: $this->tableThemes,
            attributes: $this->tableAttributes,
        );

        return [
            'head' => $head,
            'body' => $body,
            'table' => $table,
        ];
    }

    public function getComponent(): ?CompoundComponent
    {
        $components = $this->components();

        return $components['table'] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function toArray(): ?array
    {
        return $this->getComponent()?->toArray();
    }

    /**
     * @return array<int, string|int>
     */
    private function resolveHeaders(): array
    {
        if ($this->headers !== null) {
            return $this->headers;
        }

        return array_keys($this->normalizeCells($this->rows[0]));
    }

    /**
     * @param  array<int, string|int>  $headers
     * @return array<int, CompoundComponent>
     */
    private function buildHeaders(array $headers): array
    {
        $cells = [];

        foreach ($headers as $header) {
            $cells[] = $this->util->header(
                header: $header,
                type: $this->types->header,
                themes: $this->headerThemes,
            );
        }

        return $cells;
    }

    /**
     * @param  array<int, string|int>  $headers
     * @return array<int, CompoundComponent>
     */
    private function buildRows(array $headers): array
    {
        $rows = [];

        foreach ($this->rows as $index => $row) {
            $attributes = $row instanceof TableRow ? $row->attributes : [];

            if ($this->rowAttributes instanceof Closure) {
                $attributes = array_merge($attributes, ($this->rowAttributes)($row, $index));
            }

            $rows[] = $this->util->row(
                cells: $this->orderCells($this->normalizeCells($row), $headers),
                type: $this->types->row,
                themes: $this->rowThemes,
                attributes: $attributes,
            );
        }

        return $rows;
    }

    /**
     * @param  array<string|int, mixed>  $cells
     * @param  array<int, string|int>  $headers
     * @return array<int, BackendComponent>
     */
    private function orderCells(array $cells, array $headers): array
    {
        $ordered = [];

        foreach ($headers as $header) {
            if (! array_key_exists($header, $cells)) {
                if ($this->strict) {
                    throw new Exception('Missing cell for header "'.$header.'".');
                }

                $ordered[] = $this->emptyCell();

                continue;
            }

            $ordered[] = $this->resolveCell($cells[$header]);
        }

        return $ordered;
    }

    private function resolveCell(mixed $cell): BackendComponent
    {
        if ($cell instanceof BackendComponent) {
            return $cell;
        }

        if (is_string($cell) || is_int($cell)) {
            return $this->util->resolveComponent([$cell], $this->types->cell);
        }

        return $this->emptyCell();
    }

    private function emptyCell(): CompoundComponent
    {
        return $this->util->resolveComponent([], $this->types->cell);
    }

    /**
     * @param  TableRow|DataContainerInterface<mixed>|array<string|int, mixed>  $row
     * @return array<string|int, mixed>
     */
    private function normalizeCells(TableRow|DataContainerInterface|array $row): array
    {
        if ($row instanceof TableRow) {
            $row = $row->cells;
        }

        if ($row instanceof DataContainerInterface) {
            return $row->toArray();
        }

        return $row;
    }
}
