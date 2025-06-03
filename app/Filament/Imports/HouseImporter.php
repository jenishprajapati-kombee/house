<?php

namespace App\Filament\Imports;

use App\Models\House;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class HouseImporter extends Importer
{
    protected static ?string $model = House::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('listing_type')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('listing_status')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('full_street_address')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('city')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('state_province')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('zip_postal_code')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('property_type')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('bedrooms')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('bathrooms')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('square_footage')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('property_description')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('primary_photo')
                ->rules(['max:255']),
            ImportColumn::make('photos'),
            ImportColumn::make('listing_price')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('rental_price')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('rental_period')
                ->rules(['max:255']),
            ImportColumn::make('security_deposit')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('availability_date')
                ->rules(['date']),
            ImportColumn::make('contact_person_name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('contact_phone')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('contact_email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255']),
        ];
    }

    public function resolveRecord(): ?House
    {
        // return House::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new House();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your house import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
