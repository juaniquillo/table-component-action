<?php

declare(strict_types=1);

namespace Juaniquillo\TableComponentAction\Utils;

use Exception;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\BaseBackendComponent\Contracts\CompoundComponent;
use Juaniquillo\BaseBackendComponent\Contracts\ContentComponent;
use Juaniquillo\BaseBackendComponent\Contracts\ThemeComponent;

/**
 * Assembles a complete table from prebuilt row components.
 *
 * Rows keep the order given by the caller and cells keep the order produced
 * by TableCellsAction. Headers describe the <thead> only.
 */
final class TableBuilder
{
    /**
     * @var array<int|string, BackendComponent&ContentComponent&ThemeComponent>
     */
    private readonly array $rows;

    /**
     * @var array<int, string|int|BackendComponent|CompoundComponent>|null
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
     * @param  iterable<int|string, BackendComponent&ContentComponent&ThemeComponent>  $rows
     * @param  array<int, string|int|BackendComponent|CompoundComponent>|null  $headers
     */
    public function __construct(
        iterable $rows,
        ?array $headers = null,
        ?TableComponentUtil $util = null,
        ?TableTypes $types = null,
    ) {
        $this->rows = is_array($rows) ? $rows : iterator_to_array($rows);
        $this->headers = $headers === null ? null : array_values($headers);
        $this->util = $util ?? new TableComponentUtil;
        $this->types = $types ?? new TableTypes;
    }

    /**
     * @param  iterable<int|string, BackendComponent&ContentComponent&ThemeComponent>  $rows
     * @param  array<int, string|int|BackendComponent|CompoundComponent>|null  $headers
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
     * @return array{
     *   head: BackendComponent&ContentComponent&ThemeComponent,
     *   body: BackendComponent&ContentComponent&ThemeComponent,
     *   table: BackendComponent&ContentComponent&ThemeComponent
     * }|null
     */
    public function components(): ?array
    {
        if ($this->rows === []) {
            return null;
        }

        $headers = $this->resolveHeaders();

        $this->assertCellCounts($headers);

        $head = $this->util->head(
            headers: $this->buildHeaders($headers),
            type: $this->types->head,
            themes: $this->headThemes,
            rowType: $this->types->row,
        );

        $body = $this->util->body(
            rows: $this->rows,
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

    public function getComponent(): (BackendComponent&ContentComponent&ThemeComponent)|null
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
     * @return array<int, string|int|BackendComponent|CompoundComponent>
     */
    private function resolveHeaders(): array
    {
        if ($this->headers === null || $this->headers === []) {
            throw new Exception('Headers are required when building a table from prebuilt rows.');
        }

        return $this->headers;
    }

    /**
     * @param  array<int, string|int|BackendComponent|CompoundComponent>  $headers
     */
    private function assertCellCounts(array $headers): void
    {
        $expected = count($headers);

        foreach ($this->rows as $key => $row) {
            $actual = count($row->getContents());

            if ($actual !== $expected) {
                throw new Exception(sprintf(
                    'Row "%s" has %d cells but %d headers were provided.',
                    (string) $key,
                    $actual,
                    $expected,
                ));
            }
        }
    }

    /**
     * @param  array<int, string|int|BackendComponent|CompoundComponent>  $headers
     * @return array<int, BackendComponent>
     */
    private function buildHeaders(array $headers): array
    {
        $components = [];

        foreach ($headers as $header) {
            $components[] = $header instanceof BackendComponent
                ? $header
                : $this->util->header(
                    header: $header,
                    type: $this->types->header,
                    themes: $this->headerThemes,
                );
        }

        return $components;
    }
}
