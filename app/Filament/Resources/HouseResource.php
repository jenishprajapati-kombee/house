<?php

namespace App\Filament\Resources;

use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Enums\RentalPeriod;
use App\Filament\Exports\HouseExporter;
use App\Filament\Imports\HouseImporter;
use App\Filament\Resources\HouseResource\Pages;
use App\Models\House;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Storage;
use Filament\Panel;
use Filament\Tables\Actions\ExportAction;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Imports\Models\Import;
use Filament\Tables\Actions\ImportAction;

class HouseResource extends Resource
{
    protected static ?string $model = House::class;
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('House Details')->tabs([
                    Tabs\Tab::make('Listing Info')
                        ->icon('heroicon-m-tag')
                        ->schema([
                            Forms\Components\Radio::make('listing_type')
                                ->label('Listing Type')
                                ->options(ListingType::class)
                                ->required()
                                ->live()
                                ->default(ListingType::SALE),
                            Forms\Components\Select::make('listing_status')
                                ->label('Listing Status')
                                ->options(ListingStatus::class)
                                ->required()
                                ->native(false)
                                ->default(ListingStatus::DRAFT),

                            // Primary Photo (Single)
                            Forms\Components\FileUpload::make('primary_photo')
                                ->label('Primary Photo')
                                ->image()
                                ->imageEditor()
                                ->directory('house-photos/primary') // Optional: Separate directory
                                ->visibility('public')
                                ->required()
                                ->columnSpanFull(),

                            // --- START: Add Multiple File Upload ---
                            Forms\Components\FileUpload::make('photos')
                                ->label('Additional Photos (Gallery)')
                                ->multiple() // Allow multiple files
                                ->reorderable() // Allow reordering
                                ->appendFiles() // Don't replace existing files on edit, add to them
                                ->image()
                                ->imageEditor()
                                ->directory('house-photos/gallery') // Store gallery images separately
                                ->visibility('public')
                                // ->maxFiles(10) // Optional: Limit number of files
                                // ->maxSize(1024 * 5) // Optional: Limit file size (e.g., 5MB)
                                ->columnSpanFull(),
                            // --- END: Add Multiple File Upload ---

                            Forms\Components\RichEditor::make('property_description')
                                ->label('Property Description')
                                ->required()
                                ->columnSpanFull(),
                        ]),
                    Tabs\Tab::make('Property & Location')
                        ->icon('heroicon-m-map-pin')
                        ->schema([
                            // ... other fields remain the same ...
                            Section::make('Location Details')->columns(2)->schema([
                                Forms\Components\TextInput::make('full_street_address')
                                    ->label('Full Street Address')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('city')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('state_province')
                                    ->label('State/Province')
                                    ->required()
                                    ->maxLength(100),
                                Forms\Components\TextInput::make('zip_postal_code')
                                    ->label('ZIP/Postal Code')
                                    ->required()
                                    ->maxLength(20),
                            ]),
                            Section::make('Property Specifics')->columns(3)->schema([
                                Forms\Components\Select::make('property_type')
                                    ->label('Property Type')
                                    ->options([
                                        'Single-Family Home' => 'Single-Family Home',
                                        'Condo' => 'Condo',
                                        'Apartment' => 'Apartment',
                                        'Townhouse' => 'Townhouse',
                                        'Multi-Family' => 'Multi-Family',
                                        'Land' => 'Land',
                                    ])
                                    ->native(false)
                                    ->required(),
                                Forms\Components\TextInput::make('bedrooms')
                                    ->label('Number of Bedrooms')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0),
                                Forms\Components\TextInput::make('bathrooms')
                                    ->label('Number of Bathrooms (Total)')
                                    ->required()
                                    ->numeric()
                                    ->step(0.5) // Allow half baths
                                    ->minValue(0),
                                Forms\Components\TextInput::make('square_footage')
                                    ->label('Square Footage (Living Area)')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->suffix('sq ft'),
                            ]),
                        ]),
                    Tabs\Tab::make('Financials')
                        ->icon('heroicon-m-currency-dollar')
                        ->schema([
                            // ... other fields remain the same ...
                            Forms\Components\TextInput::make('listing_price')
                                ->label('Listing Price')
                                ->numeric()
                                ->prefix('$')
                                ->required(fn(Get $get): bool => $get('listing_type') === ListingType::SALE)
                                ->visible(fn(Get $get): bool => $get('listing_type') === ListingType::SALE),
                            Forms\Components\TextInput::make('rental_price')
                                ->label('Rental Price')
                                ->numeric()
                                ->prefix('$')
                                ->required(fn(Get $get): bool => $get('listing_type') === ListingType::RENT)
                                ->visible(fn(Get $get): bool => $get('listing_type') === ListingType::RENT),
                            Forms\Components\Select::make('rental_period')
                                ->label('Rental Period')
                                ->options(RentalPeriod::class)
                                ->default(RentalPeriod::MONTHLY)
                                ->native(false)
                                ->required(fn(Get $get): bool => $get('listing_type') === ListingType::RENT)
                                ->visible(fn(Get $get): bool => $get('listing_type') === ListingType::RENT),
                            Forms\Components\TextInput::make('security_deposit')
                                ->label('Security Deposit')
                                ->numeric()
                                ->prefix('$')
                                ->required(fn(Get $get): bool => $get('listing_type') === ListingType::RENT)
                                ->visible(fn(Get $get): bool => $get('listing_type') === ListingType::RENT),
                            Forms\Components\DatePicker::make('availability_date')
                                ->label('Availability Date')
                                ->required(fn(Get $get): bool => $get('listing_type') === ListingType::RENT)
                                ->visible(fn(Get $get): bool => $get('listing_type') === ListingType::RENT),
                        ]),
                    Tabs\Tab::make('Contact Info')
                        ->icon('heroicon-m-user-circle')
                        ->schema([
                            // ... other fields remain the same ...
                            Section::make('Contact Details')->columns(3)->schema([
                                Forms\Components\TextInput::make('contact_person_name')
                                    ->label('Contact Person/Agent Name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('contact_phone')
                                    ->label('Contact Phone')
                                    ->tel()
                                    ->required()
                                    ->maxLength(30),
                                Forms\Components\TextInput::make('contact_email')
                                    ->label('Contact Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        ]),
                ])->columnSpanFull(),
            ]);
    }

    // The table() method does not need changes for this,
    // as we are typically only showing the primary photo in the list view.
    public static function table(Table $table): Table
    {
        return $table
            // ... columns definition remains the same ...
            ->columns([
                Tables\Columns\ImageColumn::make('primary_photo_url') // Use accessor
                    ->label('Photo')
                    ->disk('public') // Specify the disk
                    ->defaultImageUrl(url('/images/placeholder-house.png')), // Optional placeholder
                Tables\Columns\TextColumn::make('full_street_address')
                    ->label('Address')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn(House $record): string => $record->full_street_address), // Show full on hover
                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('state_province')
                    ->label('State')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('listing_type') // Use BadgeColumn for enums
                    ->label('Type')
                    ->colors([ // Use enum for colors
                        'primary' => ListingType::SALE,
                        'warning' => ListingType::RENT,
                    ])
                    ->formatStateUsing(fn(ListingType $state): string => $state->getLabel()) // Format using Enum method
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('listing_status') // <-- This is BadgeColumn
                    ->label('Status')
                    ->colors([ // ADD THIS EXPLICIT ARRAY MAP
                        'success' => ListingStatus::ACTIVE,
                        'warning' => ListingStatus::PENDING,
                        'gray'    => ListingStatus::DRAFT,
                        'info'    => fn($state) => in_array($state, [ListingStatus::SOLD, ListingStatus::RENTED]), // Use closure for multiple states matching one color
                    ])
                    ->formatStateUsing(fn(ListingStatus $state): string => $state->getLabel())
                    ->sortable(),
                Tables\Columns\TextColumn::make('listing_price')
                    ->label('Price')
                    ->money('USD') // Format as currency
                    ->sortable()
                    ->visible(fn(?House $record): bool => $record?->listing_type === ListingType::SALE),
                Tables\Columns\TextColumn::make('rental_price')
                    ->label('Rent')
                    ->money('USD')
                    ->formatStateUsing(fn(House $record): string => $record->rental_price ? '$' . number_format($record->rental_price, 2) . ' / ' . $record->rental_period?->getLabel() : '-') // Added nullsafe operator for rental_period
                    ->sortable()
                    ->visible(fn(?House $record): bool => $record?->listing_type === ListingType::RENT),
                Tables\Columns\TextColumn::make('bedrooms')
                    ->label('Beds')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bathrooms')
                    ->label('Baths')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // ... filters remain the same ...
                SelectFilter::make('listing_type')
                    ->options(ListingType::class),
                SelectFilter::make('listing_status')
                    ->options(ListingStatus::class)->searchable(),
                SelectFilter::make('property_type')
                    ->options([
                        'Single-Family Home' => 'Single-Family Home',
                        'Condo' => 'Condo',
                        'Apartment' => 'Apartment',
                        'Townhouse' => 'Townhouse',
                        'Multi-Family' => 'Multi-Family',
                        'Land' => 'Land',
                    ])
                    ->multiple()
                    ->label('Property Type'),
            ])
            ->actions([
                // ... actions remain the same ...
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(HouseExporter::class)
                    ->label('Export')
                    ->icon('heroicon-m-arrow-down-on-square')
                    ->color('success')
                    ->modalHeading('Export Houses')
                    ->modalDescription('Export houses to a CSV file')
                    ->fileName(fn(Export $export): string => "houses-{$export->getKey()}.csv"),
                ImportAction::make()
                    ->importer(HouseImporter::class)
                    ->label('Import')
                    ->icon('heroicon-m-arrow-up-on-square')
                    ->color('success')
                    ->modalHeading('Import Houses')
                    ->modalDescription('Import houses from a CSV file')
                //->fileName(fn(Import $import): string => "houses-{$import->getKey()}.csv"),

            ])
            ->bulkActions([
                // ... bulk actions remain the same ...
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListHouses::route('/'),
            'create' => Pages\CreateHouse::route('/create'),
            'view' => Pages\ViewHouse::route('/{record}'),
            'edit' => Pages\EditHouse::route('/{record}/edit'),
        ];
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            // ...
            ->globalSearch(false);
    }
}

    // Optional: Eager load relationships for performance
    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    // }
