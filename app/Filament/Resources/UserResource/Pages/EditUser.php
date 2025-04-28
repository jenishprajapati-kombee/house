<?php
namespace App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash; // Import Hash

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    // Optional: Hash password before saving *only if* it was changed
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Check if a new password was actually entered
        if (isset($data['password']) && filled($data['password'])) {
             $data['password'] = Hash::make($data['password']);
        } else {
            // If password field exists but is empty, remove it
            // so the existing password is not overwritten with null/empty hash
            unset($data['password']);
        }
         // If using password confirmation, remove it here
         // unset($data['password_confirmation']);
        return $data;
    }
}