<?php

declare(strict_types=1);

it('ships a valid Boost skill', function () {
    $path = __DIR__.'/../../../resources/boost/skills/table-component-action/SKILL.md';

    expect(file_exists($path))->toBeTrue();

    $contents = file_get_contents($path);

    expect($contents)->toBeString()
        ->and($contents)->toMatch('/^---\r?\n(.*?)\r?\n---\r?\n/s');

    preg_match('/^---\r?\n(.*?)\r?\n---\r?\n/s', (string) $contents, $matches);

    $frontmatter = $matches[1] ?? '';

    expect($frontmatter)->toMatch('/^name: table-component-action\r?$/m')
        ->and($frontmatter)->toMatch('/^description: .+\r?$/m');
});
