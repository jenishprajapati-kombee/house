<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ListingStatus: string implements HasLabel, HasColor // <--- Check implements HasColor
{
    case ACTIVE = 'Active';
    case PENDING = 'Pending';
    case DRAFT = 'Draft';
    case RENTED = 'Rented';
    case SOLD = 'Sold';

    public function getLabel(): ?string
    {
        return $this->value;
    }

    // ---> Check this method exists and returns string/array/null <---
    public function getColor(): string | array | null
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::PENDING => 'warning',
            self::DRAFT => 'gray',
            self::RENTED, self::SOLD => 'info',
            default => null, // Optional default
        };
    }
}