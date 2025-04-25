<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\ListingType;   // Import Enum
use App\Enums\ListingStatus; // Import Enum
use App\Enums\RentalPeriod;  // Import Enum
use Illuminate\Database\Eloquent\Casts\Attribute; // For casting photo URL

class House extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_type',
        'listing_status',
        'full_street_address',
        'city',
        'state_province',
        'zip_postal_code',
        'property_type',
        'bedrooms',
        'bathrooms',
        'square_footage',
        'property_description',
        'primary_photo',
        'listing_price',
        'rental_price',
        'rental_period',
        'security_deposit',
        'availability_date',
        'contact_person_name',
        'contact_phone',
        'contact_email',
    ];

    protected $casts = [
        'listing_type' => ListingType::class,     // Cast to Enum
        'listing_status' => ListingStatus::class,   // Cast to Enum
        'rental_period' => RentalPeriod::class,    // Cast to Enum
        'bedrooms' => 'integer',
        'bathrooms' => 'decimal:1',
        'square_footage' => 'integer',
        'listing_price' => 'decimal:2',
        'rental_price' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'availability_date' => 'date',
    ];

    // Accessor to get the full URL for the primary photo
    protected function primaryPhotoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->primary_photo ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->primary_photo) : null,
        );
    }
}