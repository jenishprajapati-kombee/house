<?php

namespace App\Filament\Resources\HouseResource\Widgets;

use App\Models\House;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class HousesByCityChart extends ChartWidget
{
    protected static ?string $heading = 'Houses by City';

    protected static ?int $sort = 3; // Display below the creation chart

    // --- Add these properties ---
    protected static ?string $maxHeight = '300px'; // Suggest a maximum height (adjust as needed)

    protected int | string | array $columnSpan = 'md'; // Make it take up half width on medium screens and up
    // You could use '6' directly for half width: protected int | string | array $columnSpan = 6;
    // Or more specific responsive: protected int | string | array $columnSpan = ['md' => 6, 'xl' => 6];

    // --- Properties end ---


    protected function getData(): array
    {
        // Query to get count of houses grouped by city
        $data = House::query()
            ->select('city', DB::raw('count(*) as count'))
            ->groupBy('city')
            ->orderBy('count', 'desc') // Optional: order by count
            ->limit(10) // Optional: Limit to top N cities to avoid clutter
            ->pluck('count', 'city') // Get results as ['CityName' => count]
            ->toArray();

        // Ensure data exists to avoid errors if no houses
        if (empty($data)) {
             return [
                 'datasets' => [
                     [
                         'label' => 'No data',
                         'data' => [0],
                     ],
                 ],
                 'labels' => ['No houses found'],
             ];
        }


        return [
            'datasets' => [
                [
                    // 'label' => 'Houses', // Label not typically needed for pie/doughnut dataset
                    'data' => array_values($data), // Get just the counts
                    // Optional: Define background colors for slices
                    'backgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#C9CBCF', '#FFCD56', '#7FCDAE', '#F28B82'
                    ],
                    'hoverBackgroundColor' => [ // Optional: darker on hover
                        '#FF4F73', '#2C91DA', '#FFC340', '#3FA9A9', '#8B4FFF',
                        '#FF902D', '#B8BBBE', '#FFC340', '#6CBBA0', '#EE736A'
                    ],
                ],
            ],
            'labels' => array_keys($data), // Get the city names as labels
        ];
    }

    protected function getType(): string
    {
        // Keep pie or switch back to doughnut if preferred
        return 'pie';
       //return 'doughnut';
    }

     // Optional: Add Chart Options to disable aspect ratio maintenance if needed
     // protected function getOptions(): array
     // {
     //     return [
     //         'maintainAspectRatio' => false,
     //     ];
     // }
}