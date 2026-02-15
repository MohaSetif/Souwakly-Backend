<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('affiliate_id')
                    ->label('Affiliate')
                    ->relationship('affiliate', 'name', fn(\Illuminate\Database\Eloquent\Builder $query) => $query->role('Affiliate'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('merchant_id')
                    ->label('Merchant')
                    ->relationship('merchant', 'name', fn(\Illuminate\Database\Eloquent\Builder $query) => $query->role('Merchant'))
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('pending')
                    ->required(),
            ]);
    }
}
