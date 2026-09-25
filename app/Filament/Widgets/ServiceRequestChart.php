<?php

namespace App\Filament\Widgets;

use App\Models\ServiceRequest;
use App\Models\ServiceType;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class ServiceRequestChart extends ChartWidget
{
    protected ?string $heading = 'Permohonan per Bulan';

    protected static ?int $sort = 2;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $serviceTypes = ServiceType::all();
        $datasets = [];
        $colors = ['#22c55e', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'];

        foreach ($serviceTypes as $index => $type) {
            $data = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $data[] = ServiceRequest::where('service_type_id', $type->id)
                    ->whereMonth('request_date', $month->month)
                    ->whereYear('request_date', $month->year)
                    ->count();
            }

            $datasets[] = [
                'label' => $type->name,
                'data' => $data,
                'borderColor' => $colors[$index % count($colors)],
                'backgroundColor' => $colors[$index % count($colors)].'33',
                'fill' => true,
            ];
        }

        $labels = [];
        for ($i = 11; $i >= 0; $i--) {
            $labels[] = Carbon::now()->subMonths($i)->translatedFormat('M Y');
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
