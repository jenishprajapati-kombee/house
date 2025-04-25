<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ListingType; // Import Enum
use App\Enums\ListingStatus; // Import Enum
use App\Enums\RentalPeriod; // Import Enum

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('houses', function (Blueprint $table) {
            $table->id();

            // Core Listing Essentials
            $table->string('listing_type')->default(ListingType::SALE->value); // Use enum values
            $table->string('listing_status')->default(ListingStatus::DRAFT->value); // Use enum values
            $table->string('full_street_address');
            $table->string('city');
            $table->string('state_province');
            $table->string('zip_postal_code');
            $table->string('property_type'); // Consider Enum if options are fixed
            $table->unsignedInteger('bedrooms');
            $table->decimal('bathrooms', 3, 1); // e.g., 2.5 baths
            $table->unsignedInteger('square_footage');
            $table->text('property_description');
            $table->string('primary_photo')->nullable(); // Store path to photo

            // Financial Essentials
            $table->decimal('listing_price', 15, 2)->nullable(); // For Sale
            $table->decimal('rental_price', 15, 2)->nullable(); // For Rent
            $table->string('rental_period')->nullable()->default(RentalPeriod::MONTHLY->value); // Use enum values
            $table->decimal('security_deposit', 15, 2)->nullable(); // For Rent
            $table->date('availability_date')->nullable(); // For Rent

            // Contact Essentials
            $table->string('contact_person_name');
            $table->string('contact_phone');
            $table->string('contact_email');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('houses');
    }
};