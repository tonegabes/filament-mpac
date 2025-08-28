<?php

declare(strict_types=1);

namespace App\Filament\Resources\Media;

use App\Enums\NavGroups;
use App\Filament\Resources\Media\Pages\CreateMedia;
use App\Filament\Resources\Media\Pages\EditMedia;
use App\Filament\Resources\Media\Pages\ListMedia;
use App\Filament\Resources\Media\Pages\ViewMedia;
use App\Filament\Resources\Media\Schemas\MediaForm;
use App\Filament\Resources\Media\Schemas\MediaInfolist;
use App\Filament\Resources\Media\Tables\MediaTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $modelLabel = 'Mídia';

    protected static string|BackedEnum|null $navigationIcon = Phosphor::File;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string
    {
        return NavGroups::Files->value;
    }

    public static function form(Schema $schema): Schema
    {
        return MediaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MediaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MediaTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedia::route('/'),
            'view'  => ViewMedia::route('/{record}'),
            // 'create' => CreateMedia::route('/create'),
            // 'edit'   => EditMedia::route('/{record}/edit'),
        ];
    }
}
