<?php

namespace App\Filament\Resources\HouseResource\Widgets;

use App\Models\House;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend; // Import Trend
use Flowframe\Trend\TrendValue; // Import TrendValue

class HouseCreationChart extends ChartWidget
{
    protected static ?string $heading = 'House Creation Trends';

    protected static ?int $sort = 2; // Display below the stats overview

    protected function getData(): array
    {
        // Use the Trend package to get data per month for the last year
        $data = Trend::model(House::class)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth() // Or perDay(), perWeek()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Houses Created',
                    // Map the TrendValue objects to get just the counts
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => 'rgb(54, 162, 235)', // Example color
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)', // Example color with transparency
                ],
            ],
            // Map the TrendValue objects to get the date labels
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line'; // Type of chart (line, bar, pie, doughnut, etc.)
    }
}