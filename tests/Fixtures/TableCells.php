<?php

declare(strict_types=1);

namespace Juaniquillo\TableComponentAction\Tests\Fixtures;

use Juaniquillo\BackendComponents\Enums\ComponentEnum;
use Juaniquillo\BackendComponents\MainBackendComponent;

final class TableCells
{
    public static function td(string $content): MainBackendComponent
    {
        return (new MainBackendComponent(ComponentEnum::TD))->setContent($content);
    }
}
