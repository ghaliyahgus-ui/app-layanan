<?php

namespace App\Filament\Widgets;

use App\Enums\ServiceRequestStatus;
use App\Models\ServiceRequest;
use Filament\Widgets\ChartWidget;

class StatusDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Distribusi Status Permohonan';

    protected static ?int $sort = 3;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $statuses = ServiceRequestStatus::cases();
        $data = [];
        $labels = [];
        $colors = [];

        $colorMap = [
            'gray' => '#6b7280',
            'info' => '#3b82f6',
            'warning' => '#f59e0b',
            'primary' => '#22c55e',
            'success' => '#10b981',
            'danger' => '#ef4444',
        ];

        foreach ($statuses as $status) {
            $count = ServiceRequest::where('status', $status->value)->count();
            if ($count > 0) {
                $data[] = $count;
                $labels[] = $status->label();
                $colors[] = $colorMap[$status->color()] ?? '#6b7280';
            }
        }

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
