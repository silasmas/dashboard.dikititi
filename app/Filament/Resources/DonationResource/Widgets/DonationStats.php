<?php

namespace App\Filament\Resources\DonationResource\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Illuminate\Support\Facades\DB;

class DonationStats extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        // On groupe les montants par devise
        $amounts = DB::table('donations')
            ->select('currency', DB::raw('SUM(amount) as total'))
            ->groupBy('currency')
            ->get();

        $cards = [];

        foreach ($amounts as $amount) {
            $symbol = $this->getCurrencySymbol($amount->currency);

            $cards[] = Card::make(
                "Total {$amount->currency}",
                number_format($amount->total, 2, ',', ' ') . ' ' . $symbol
            );
        }

        return $cards;
    }

    // Petite fonction pour ajouter un symbole selon la devise
    protected function getCurrencySymbol(string $currency): string
    {
        return match (strtoupper($currency)) {
            'USD' => '$',
            'EUR' => '€',
            'CDF' => 'FC',
            default => $currency, // fallback si non connu
        };
    }
}
