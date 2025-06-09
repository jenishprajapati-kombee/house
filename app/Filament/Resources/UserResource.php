<?php

namespace App\Filament\Resources;

use App\Filament\Imports\UserImporter;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers; // Keep if you add relation managers later
use App\Models\User;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash; // Import Hash facade for password handling
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn; // For boolean/timestamp checks
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section; // For form organization
use Filament\Tables\Actions\ImportAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users'; // Changed icon

    protected static ?string $navigationGroup = 'User Management'; // Group in sidebar

    protected static ?int $navigationSort = 1; // Position within the group

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('User Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            // Add uniqueness check, ignore current record on edit
                            ->unique(ignoreRecord: true),
                        TextInput::make('mobile_number')
                            ->tel() // Use tel input type
                            ->maxLength(20)
                            // Add uniqueness check, ignore current record on edit
                            ->unique(ignoreRecord: true)
                            ->nullable(), // Make nullable if it can be empty
                        TextInput::make('nationality')
                            ->maxLength(100)
                            ->nullable(), // Make nullable
                    ]),

                Section::make('Password')
                    ->description('Leave blank to keep the current password when editing.')
                    ->schema([
                        TextInput::make('password')
                            ->password()
                            // Only required on create, not on edit
                            ->required(fn(string $context): bool => $context === 'create')
                            // Hashing is done in Pages/CreateUser or Pages/EditUser typically
                            // Or using mutateDehydratedStateUsing
                            ->dehydrateStateUsing(fn(?string $state): ?string => filled($state) ? Hash::make($state) : null)
                            // Do not reveal password hash on edit
                            ->dehydrated(fn(?string $state): bool => filled($state))
                            ->revealable(), // Add eye icon to show/hide
                        // Add password confirmation rule for create page
                        // ->confirmed(fn (string $context): bool => $context === 'create'), // Requires a 'password_confirmation' field
                    ])->visibleOn(['create', 'edit']), // Show only on create/edit forms

                Section::make('Metadata')
                    ->columns(2)
                    ->visibleOn('view') // Show only on the view page
                    ->schema([
                        DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->disabled()
                            ->readOnly(),
                        DateTimePicker::make('created_at')
                            ->disabled()
                            ->readOnly(),
                        DateTimePicker::make('updated_at')
                            ->disabled()
                            ->readOnly(),
                    ]),

                // Add Role Selection if using Spatie Permissions
                // Section::make('Roles')
                //     ->schema([
                //         Forms\Components\Select::make('roles')
                //             ->relationship('roles', 'name')
                //             ->multiple()
                //             ->preload() // Load options initially
                //             ->searchable()
                //             // ->required() // If a user must have at least one role
                //     ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //TextColumn::make('id')->sortable(), // Good to have ID
                TextColumn::make('name')
                    ->searchable()
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                TextColumn::make('mobile_number')
                    ->searchable()
                    ->searchable(isIndividual: true)
                    ->placeholder('N/A'), // Show placeholder if null
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Hide by default
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true), // Hide by default
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email Verified')
                    ->nullable(), // Allows filtering by verified, not verified, or either
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Add delete action
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Add Relation Managers here if needed (e.g., for roles)
            // RelationManagers\RolesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        // These should be correctly generated by the command
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
