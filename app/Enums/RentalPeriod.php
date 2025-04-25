<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RentalPeriod: string implements HasLabel
{
    case MONTHLY = 'Monthly';
    case WEEKLY = 'Weekly';
    case YEARLY = 'Yearly';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}