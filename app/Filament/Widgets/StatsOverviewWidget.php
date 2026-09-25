<?php

namespace App\Filament\Widgets;

use App\Enums\ServiceRequestStatus;
use App\Models\ServiceRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $total = ServiceRequest::count();
        $followedUp = ServiceRequest::where('status', '!=', ServiceRequestStatus::Submitted->value)->count();
        $pending = ServiceRequest::where('status', ServiceRequestStatus::Submitted->value)->count();
        $completedThisMonth = ServiceRequest::where('status', ServiceRequestStatus::Completed->value)
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        return [
            Stat::make('Total Pemohon', $total)
                ->description('Seluruh permohonan')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Ditindaklanjuti', $followedUp)
                ->description(($total > 0 ? round(($followedUp / $total) * 100) : 0).'% dari total')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Menunggu Konfirmasi', $pending)
                ->description('Belum diproses')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Selesai Bulan Ini', $completedThisMonth)
                ->description('Bulan '.now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-trophy')
                ->color('info'),
        ];
    }
}
