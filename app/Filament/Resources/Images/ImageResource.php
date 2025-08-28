<?php

declare(strict_types=1);

namespace App\Filament\Resources\Images;

use App\Enums\NavGroups;
use App\Filament\Resources\Images\Pages\CreateImage;
use App\Filament\Resources\Images\Pages\EditImage;
use App\Filament\Resources\Images\Pages\ListImages;
use App\Filament\Resources\Images\Pages\ViewImage;
use App\Filament\Resources\Images\Schemas\ImageForm;
use App\Filament\Resources\Images\Schemas\ImageInfolist;
use App\Filament\Resources\Images\Tables\ImagesTable;
use App\Models\Image;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ImageResource extends Resource
{
    protected static ?string $model = Image::class;

    protected static ?string $modelLabel = 'Imagem';

    protected static ?string $pluralModelLabel = 'Imagens';

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Image;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string
    {
        return NavGroups::Files->value;
    }

    public static function form(Schema $schema): Schema
    {
        return ImageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ImageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ImagesTable::configure($table);
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
            'index'  => ListImages::route('/'),
            'create' => CreateImage::route('/create'),
            'view'   => ViewImage::route('/{record}'),
            'edit'   => EditImage::route('/{record}/edit'),
        ];
    }
}
