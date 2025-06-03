<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Imports\UserImporter;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\HtmlString;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ImportAction::make()
                ->importer(UserImporter::class)
                ->label('Import')
                ->icon('heroicon-m-arrow-up-on-square')
                ->color('success')
                ->modalHeading('Import Users')
                ->modalDescription('Import users from a CSV file')
                ->modalContent(new HtmlString(
                    '<a href="' . route('download-example-csv') . '" class="text-yellow-400 hover:underline mb-4 inline-block">Download example CSV file</a>'
                )),
            Actions\CreateAction::make(),
        ];
    }
}
