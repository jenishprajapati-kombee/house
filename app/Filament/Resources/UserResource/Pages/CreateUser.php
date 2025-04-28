<?php
namespace App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash; // Import Hash

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    // Optional: Hash password before creation
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['password']) && filled($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
             // Remove password key if it's empty to avoid issues
            unset($data['password']);
        }
        // If using password confirmation, remove it here
        // unset($data['password_confirmation']);
        return $data;
    }
}