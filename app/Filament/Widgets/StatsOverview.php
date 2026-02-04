<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Merchants', User::role('Merchant')->count())
                ->description('Active merchants')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('warning'),
            Stat::make('Affiliates', User::role('Affiliate')->count())
                ->description('Active affiliates')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            Stat::make('Products', Product::count())
                ->description('Total products')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),
            Stat::make('Total Orders', Order::count())
                ->description('All orders')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('gray'),
            Stat::make('Confirmed Orders', Order::where('status', 'confirmed')->count())
                ->description('Successfully confirmed')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
