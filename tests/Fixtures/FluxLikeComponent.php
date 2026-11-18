<?php

declare(strict_types=1);

namespace Juaniquillo\TableComponentAction\Tests\Fixtures;

use BackedEnum;
use LogicException;
use Juaniquillo\BackendComponents\Concerns\HasContent;
use Juaniquillo\BackendComponents\Concerns\IsBackendComponent;
use Juaniquillo\BackendComponents\Themes\DefaultThemeManager;
use Juaniquillo\BaseBackendComponent\Concerns\IsThemeable;
use Juaniquillo\BaseBackendComponent\Contracts\AttributeBag;
use Juaniquillo\BaseBackendComponent\Contracts\BackendComponent;
use Juaniquillo\BaseBackendComponent\Contracts\ContentComponent;
use Juaniquillo\BaseBackendComponent\Contracts\ThemeComponent;
use Juaniquillo\BaseBackendComponent\Contracts\ThemeManager;

/**
 * Mirrors FluxBackendComponent: it satisfies the contracts the table package
 * needs without implementing CompoundComponent.
 */
final class FluxLikeComponent implements BackendComponent, ContentComponent, ThemeComponent
{
    use HasContent;
    use IsBackendComponent;
    use IsThemeable;

    public function __construct(
        private readonly string|BackedEnum $name,
        ThemeManager $themeManager = new DefaultThemeManager,
    ) {
        $this->themeManager = $themeManager;
    }

    public function getAttributeBag(): AttributeBag
    {
        throw new LogicException('Not used in tests.');
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name instanceof BackedEnum ? $this->name->value : $this->name,
            'component' => self::class,
            'attributes' => $this->getAttributes(),
            'contents' => $this->processContent()->toArray(),
            'theme' => [
                'manager' => $this->getThemeManager()::class,
                'themes' => $this->getThemes(),
                'path' => $this->getThemeManager()->getDefaultPath(),
                'realPath' => $this->getThemeManager()->getThemePath(),
            ],
            'path' => null,
            'settings' => [],
        ];
    }
}
