<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\ListingType;
use App\Enums\ListingStatus;
use App\Enums\RentalPeriod;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute; // Import Attribute for accessor/mutator syntax

class House extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'listing_type',
        'listing_status',
        'primary_photo',
        'photos', // <-- ADD 'photos' HERE
        'property_description',
        'full_street_address',
        'city',
        'state_province',
        'zip_postal_code',
        'property_type',
        'bedrooms',
        'bathrooms',
        'square_footage',
        'listing_price',
        'rental_price',
        'rental_period',
        'security_deposit',
        'availability_date',
        'contact_person_name',
        'contact_phone',
        'contact_email',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'listing_type' => ListingType::class,
        'listing_status' => ListingStatus::class,
        'rental_period' => RentalPeriod::class,
        'availability_date' => 'date',
        'listing_price' => 'decimal:2',
        'rental_price' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'bathrooms' => 'decimal:1',
        'square_footage' => 'integer',
        'bedrooms' => 'integer',
        'photos' => 'array', // <-- ADD CAST FOR 'photos'
    ];

    /**
     * Accessor for the primary photo URL.
     */
    public function getPrimaryPhotoUrlAttribute(): ?string
    {
        if ($this->primary_photo) {
            return Storage::disk('public')->url($this->primary_photo);
        }
        // Return a default placeholder if needed
        return url('/images/placeholder-house.png');
    }

    // OPTIONAL: Accessor to get full URLs for gallery photos
    public function getPhotoUrlsAttribute(): array
    {
        $urls = [];
        if ($this->photos) {
            foreach ($this->photos as $path) {
                $urls[] = Storage::disk('public')->url($path);
            }
        }
        return $urls;
    }
}