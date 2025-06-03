<?php

namespace App\Filament\Exports;

use App\Models\House;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class HouseExporter extends Exporter
{
    protected static ?string $model = House::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('listing_type'),
            ExportColumn::make('listing_status'),
            ExportColumn::make('full_street_address'),
            ExportColumn::make('city'),
            ExportColumn::make('state_province'),
            ExportColumn::make('zip_postal_code'),
            ExportColumn::make('property_type'),
            ExportColumn::make('bedrooms'),
            ExportColumn::make('bathrooms'),
            ExportColumn::make('square_footage'),
            ExportColumn::make('property_description'),
            ExportColumn::make('primary_photo'),
            ExportColumn::make('photos'),
            ExportColumn::make('listing_price'),
            ExportColumn::make('rental_price'),
            ExportColumn::make('rental_period'),
            ExportColumn::make('security_deposit'),
            ExportColumn::make('availability_date'),
            ExportColumn::make('contact_person_name'),
            ExportColumn::make('contact_phone'),
            ExportColumn::make('contact_email'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your house export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
