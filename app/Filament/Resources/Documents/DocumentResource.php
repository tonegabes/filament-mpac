<?php

declare(strict_types=1);

namespace App\Filament\Resources\Documents;

use App\Enums\NavGroups;
use App\Filament\Resources\Documents\Pages\ListDocuments;
use App\Filament\Resources\Documents\Pages\ViewDocument;
use App\Filament\Resources\Documents\Schemas\DocumentForm;
use App\Filament\Resources\Documents\Schemas\DocumentInfolist;
use App\Filament\Resources\Documents\Tables\DocumentsTable;
use App\Models\Document;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $modelLabel = 'Documento';

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Files;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string
    {
        return NavGroups::Files->value;
    }

    public static function form(Schema $schema): Schema
    {
        return DocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocuments::route('/'),
            'view'  => ViewDocument::route('/{record}'),
        ];
    }
}
