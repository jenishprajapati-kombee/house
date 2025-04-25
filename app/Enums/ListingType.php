<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ListingType: string implements HasLabel
{
    case SALE = 'For Sale';
    case RENT = 'For Rent';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}