<?php

declare(strict_types=1);

use Juaniquillo\TableComponentAction\TableComponentActionServiceProvider;

it('registers the package service provider', function () {
    expect(app()->getProvider(TableComponentActionServiceProvider::class))
        ->not->toBeNull();
});
