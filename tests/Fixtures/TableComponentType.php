<?php

declare(strict_types=1);

namespace Juaniquillo\TableComponentAction\Tests\Fixtures;

enum TableComponentType: string
{
    case Table = 'table.table';

    case Head = 'table.thead';

    case Body = 'table.tbody';

    case Row = 'table.tr';

    case Header = 'table.th';

    case Cell = 'table.td';
}
