<?php

namespace App\Filament\Resources;

use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Enums\RentalPeriod;
use App\Filament\Resources\HouseResource\Pages;
// use App\Filament\Resources\HouseResource\RelationManagers; // Use if needed later
use App\Models\House;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get; // Import Get for reactive forms
use Filament\Forms\Components\Section; // For grouping fields
use Filament\Forms\Components\Tabs; // For better form organization
use Filament\Tables\Enums\FiltersLayout; // For filter layout
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Storage; // For file handling


class HouseResource extends Resource
{
    protected static ?string $model = House::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?int $navigationSort = 1; // Position in sidebar

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
                                ->options(ListingType::class) // Use Enum directly
                                ->required()
                                ->live() // Make reactive for conditional fields
                                ->default(ListingType::SALE), // Sensible default
                            Forms\Components\Select::make('listing_status')
                                ->label('Listing Status')
                                ->options(ListingStatus::class) // Use Enum directly
                                ->required()
                                ->native(false) // Nicer dropdown
                                ->default(ListingStatus::DRAFT),
                            Forms\Components\FileUpload::make('primary_photo')
                                ->label('Primary Photo')
                                ->image()
                                ->imageEditor() // Optional: enable image editor
                                ->directory('house-photos') // Store in storage/app/public/house-photos
                                ->visibility('public') // Make accessible via URL
                                ->required()
                                ->columnSpanFull(),
                            Forms\Components\RichEditor::make('property_description')
                                ->label('Property Description')
                                ->required()
                                ->columnSpanFull(),
                        ]),
                    Tabs\Tab::make('Property & Location')
                        ->icon('heroicon-m-map-pin')
                        ->schema([
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
                                    ->options([ // Add more as needed
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
                            // Conditional fields based on Listing Type
                            Forms\Components\TextInput::make('listing_price')
                                ->label('Listing Price')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                // Show only when listing_type is 'For Sale'
                                ->visible(fn (Get $get): bool => $get('listing_type') === ListingType::SALE)
                                ->required(fn (Get $get): bool => $get('listing_type') === ListingType::SALE),

                            Forms\Components\TextInput::make('rental_price')
                                ->label('Rental Price')
                                ->numeric()
                                ->prefix('$')
                                ->required()
                                // Show only when listing_type is 'For Rent'
                                ->visible(fn (Get $get): bool => $get('listing_type') === ListingType::RENT)
                                ->required(fn (Get $get): bool => $get('listing_type') === ListingType::RENT),
                            Forms\Components\Select::make('rental_period')
                                ->label('Rental Period')
                                ->options(RentalPeriod::class) // Use Enum
                                ->default(RentalPeriod::MONTHLY)
                                ->native(false)
                                // Show only when listing_type is 'For Rent'
                                ->visible(fn (Get $get): bool => $get('listing_type') === ListingType::RENT)
                                ->required(fn (Get $get): bool => $get('listing_type') === ListingType::RENT),
                            Forms\Components\TextInput::make('security_deposit')
                                ->label('Security Deposit')
                                ->numeric()
                                ->prefix('$')
                                // Show only when listing_type is 'For Rent'
                                ->visible(fn (Get $get): bool => $get('listing_type') === ListingType::RENT)
                                ->required(fn (Get $get): bool => $get('listing_type') === ListingType::RENT),
                            Forms\Components\DatePicker::make('availability_date')
                                ->label('Availability Date')
                                // Show only when listing_type is 'For Rent'
                                ->visible(fn (Get $get): bool => $get('listing_type') === ListingType::RENT)
                                ->required(fn (Get $get): bool => $get('listing_type') === ListingType::RENT),
                        ]),
                    Tabs\Tab::make('Contact Info')
                        ->icon('heroicon-m-user-circle')
                        ->schema([
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
                ])->columnSpanFull(), // Ensure Tabs take full width
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('primary_photo_url') // Use accessor
                    ->label('Photo')
                    ->disk('public') // Specify the disk
                    ->defaultImageUrl(url('/images/placeholder-house.png')), // Optional placeholder
                Tables\Columns\TextColumn::make('full_street_address')
                    ->label('Address')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn (House $record): string => $record->full_street_address), // Show full on hover
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
                    ->formatStateUsing(fn (ListingType $state): string => $state->getLabel()) // Format using Enum method
                    ->sortable(),
                // Tables\Columns\BadgeColumn::make('listing_status') // <-- This is BadgeColumn
                //     ->label('Status')
                //     ->colors(ListingStatus::class) // <-- This is CORRECT for BadgeColumn
                //     ->formatStateUsing(fn (ListingStatus $state): string => $state->getLabel())
                //     ->sortable(),
                 // Inside the table() method's ->columns([...]) array

                Tables\Columns\TextColumn::make('listing_price')
                    ->label('Price')
                    ->money('USD') // Format as currency
                    ->sortable()
                    // Add '?' to type hint and use '?->' nullsafe operator
                    ->visible(fn (?House $record): bool => $record?->listing_type === ListingType::SALE),

                Tables\Columns\TextColumn::make('rental_price')
                    ->label('Rent')
                    ->money('USD')
                    // Keep non-nullable here as formatStateUsing typically needs a record
                    ->formatStateUsing(fn (House $record): string => $record->rental_price ? '$'.number_format($record->rental_price, 2).' / '.$record->rental_period->getLabel() : '-')
                    ->sortable()
                    // Add '?' to type hint and use '?->' nullsafe operator
                    ->visible(fn (?House $record): bool => $record?->listing_type === ListingType::RENT),
                Tables\Columns\TextColumn::make('bedrooms')
                    ->label('Beds')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bathrooms')
                    ->label('Baths')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Hide by default
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Hide by default
            ])
            ->filters([
                SelectFilter::make('listing_type')
                    ->options(ListingType::class), // Use Enum directly
                SelectFilter::make('listing_status')
                    ->options(ListingStatus::class), // Use Enum directly
                 SelectFilter::make('property_type')
                     ->options([ // Add more as needed
                         'Single-Family Home' => 'Single-Family Home',
                         'Condo' => 'Condo',
                         'Apartment' => 'Apartment',
                         'Townhouse' => 'Townhouse',
                         'Multi-Family' => 'Multi-Family',
                         'Land' => 'Land',
                     ])
                     ->multiple() // Allow selecting multiple types
                     ->label('Property Type'),
            ], layout: FiltersLayout::AboveContent) // Better filter placement
            ->actions([
                Tables\Actions\ViewAction::make(), // Always available if user can viewAny/view
                Tables\Actions\EditAction::make(), // Visibility controlled by policy
                Tables\Actions\DeleteAction::make(), // Visibility controlled by policy
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(), // Visibility controlled by policy
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Define relation managers here if needed in the future
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHouses::route('/'),
            'create' => Pages\CreateHouse::route('/create'),
            // View page is often preferred over Edit for regular users
            // Edit page implicitly includes viewing
            'view' => Pages\ViewHouse::route('/{record}'), // Add View page route
            'edit' => Pages\EditHouse::route('/{record}/edit'),
        ];
    }

    // Optional: Eager load relationships for performance
    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    // }
}