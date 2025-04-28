<?php

namespace App\Filament\Resources\HouseResource\Widgets;

use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Models\House;
use App\Models\User; // Import the User model
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HouseStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // --- Calculate the statistics ---

        // User Count
        $totalUsers = User::count();

        // House Counts
        $totalHouses = House::count();
        $housesForSale = House::where('listing_type', ListingType::SALE)->count();
        $housesForRent = House::where('listing_type', ListingType::RENT)->count();
        $activeListings = House::where('listing_status', ListingStatus::ACTIVE)->count();
        // Combine Sold and Rented as 'Off Market' or show separately
        $soldHouses = House::where('listing_status', ListingStatus::SOLD)->count();
        $rentedHouses = House::where('listing_status', ListingStatus::RENTED)->count();
        $offMarketHouses = $soldHouses + $rentedHouses; // Or query whereIn

        // --- Create Stat objects for display ---
        return [
            Stat::make('Total Users', $totalUsers)
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Total Houses', $totalHouses)
                ->description('All listings in the system')
                ->descriptionIcon('heroicon-m-home-modern')
                ->color('info'),

            Stat::make('Houses For Sale', $housesForSale)
                ->description('Listings marked as For Sale')
                ->descriptionIcon('heroicon-m-currency-dollar') // Example icon
                ->color('success'), // Example color

            Stat::make('Houses For Rent', $housesForRent)
                ->description('Listings marked as For Rent')
                ->descriptionIcon('heroicon-m-key') // Example icon
                ->color('warning'), // Example color

            Stat::make('Active Listings', $activeListings)
                ->description('Currently active houses')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Off Market (Sold/Rented)', $offMarketHouses)
                ->description('Houses currently Sold or Rented')
                ->descriptionIcon('heroicon-m-archive-box-x-mark') // Example icon
                ->color('gray'), // Example color

            // You could show Sold and Rented separately if preferred:
            // Stat::make('Sold Houses', $soldHouses)->color('gray'),
            // Stat::make('Rented Houses', $rentedHouses)->color('gray'),
        ];
    }
}