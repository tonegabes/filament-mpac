<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Contracts\HasFileUrl;
use Filament\Actions\Action;
use Illuminate\Support\Js;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class CopyFileUrlAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'copy-file-url';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('filament-actions::copy.link.label'))
            ->icon(Phosphor::Copy)
            ->keyBindings(['mod+c'])
            ->tooltip(fn (HasFileUrl $record): string => $record->getFileUrl())
            ->alpineClickHandler(function (HasFileUrl $record): string {
                $copyableState = Js::from($record->getFileUrl());
                $copyMessageJs = Js::from(__('filament-actions::copy.link.message'));

                return <<<JS
                    window.navigator.clipboard.writeText({$copyableState})
                    \$tooltip({$copyMessageJs}, {
                        theme: \$store.theme,
                        timeout: 2000,
                    })
                JS;
            })
        ;

    }
}
