<?php

declare(strict_types=1);

namespace Juaniquillo\TableComponentAction\Utils;

use BackedEnum;
use Juaniquillo\BackendComponents\Enums\ComponentEnum;

/**
 * Component types used to wrap each table part.
 *
 * Inject a different set when using another component package
 * (for example FluxComponentEnum or SlateComponentEnum).
 */
final readonly class TableTypes
{
    public function __construct(
        public string|BackedEnum $table = ComponentEnum::TABLE,
        public string|BackedEnum $head = ComponentEnum::THEAD,
        public string|BackedEnum $body = ComponentEnum::TBODY,
        public string|BackedEnum $row = ComponentEnum::TR,
        public string|BackedEnum $header = ComponentEnum::TH,
        public string|BackedEnum $cell = ComponentEnum::TD,
    ) {}
}
