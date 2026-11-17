<?php

declare(strict_types=1);

namespace Juaniquillo\TableComponentAction\Tests\Fixtures;

use Juaniquillo\BaseBackendComponent\Concerns\IsBackendComponent;
use Juaniquillo\BaseBackendComponent\Contracts\AttributeBag;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;

/**
 * A backend component that cannot hold content, used to test the guard in
 * TableComponentUtil.
 */
final class IncompleteBackendComponent implements BackendComponent
{
    use IsBackendComponent;

    public function getAttributeBag(): AttributeBag
    {
        throw new \LogicException('Not used in tests.');
    }

    public function toArray(): array
    {
        throw new \LogicException('Not used in tests.');
    }
}
