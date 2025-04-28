<?php
namespace App\Filament\Resources;

use App\Filament\Resources\DonateResource\Pages;
use App\Models\Donation;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DonateResource extends Resource
{
    protected static ?string $model = Donation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('amount')
                    ->label('Montant')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('currency')
                    ->label('Devise')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('pricing.deadline')
                    ->label('Date de fin')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('payments.reference')
                    ->label('Référence')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('payments.telephone')
                    ->label('Téléphone')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('payments.type.type_name')
                    ->label('Type de paiement')
                    ->sortable()
                    ->searchable(),
                                                                // TextColumn::make('payments.status.status_name')
                                                                //     ->label('Statut de paiement')
                                                                //     ->sortable()
                                                                //     ->searchable(),
                BadgeColumn::make('payment.status.status_name') // <-- ici on passe à BadgeColumn
                    ->label('Statut de Paiement')
                    ->sortable()
                    ->colors([
                        'success' => fn($state) => $state === 'Validé',
                        'warning' => fn($state) => $state === 'En attente',
                        'danger'  => fn($state)  => $state === 'Rejeté',
                        'gray'    => fn($state)    => $state === null,
                    ])
                    ->icons([
                        'heroicon-o-check-circle' => 'Validé',
                        'heroicon-o-clock'        => 'En attente',
                        'heroicon-o-x-circle'     => 'Rejeté',
                    ])
                    ->searchable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                                                  // self::paymentFilters(),      // Ton filtre Type/Status
                self::paymentPeriodFilter(),      // Ton filtre Période de Paiement
                self::paymentQuickPeriodFilter(), // aujourd'hui, semaine, mois
                SelectFilter::make('currency')
                    ->label('Monnaie')
                    ->options([
                        'USD' => 'Dollars',
                        'CDF' => 'Francs',
                    ]),

                // Filtrer par Type de Payment
                SelectFilter::make('type_id')
                    ->label('Type de don')
                    ->options(function () {
                        return \App\Models\Type::whereHas('payments.donation')
                            ->pluck('type_name', 'id');
                    })
                    // ->query(function (Builder $query, $state) {
                    //     // dd($query->toSql());
                    //     if ($query) {
                    //         $query->whereHas('payments.type', function ($q) use ($state) {
                    //             $q->where('id', $state);
                    //         });
                    //     }
                    // })
                    ->searchable()
                    ->preload()
                    ->placeholder('Sélectionner un type'),

                // Filtrer par Status de Payment
                SelectFilter::make('payments.status_id')
                    ->label('Status de don')
                    // ->relationship('payments.status', 'status_name')
                    ->options(function () {
                        return \App\Models\Status::whereHas('payments.donation')
                            ->pluck('status_name', 'id');
                    })
                    ->searchable()
                    ->preload(),
                // SelectFilter::make('payment_quick_period')
                //     ->label('Période')
                //     ->options([
                //         'today' => "Aujourd'hui",
                //         'week'  => "Cette semaine",
                //         'month' => "Ce mois-ci",
                //     ])
                //     ->query(function (Builder $query, $state) {
                //         $query->whereHas('payments', function ($q) use ($state) {
                //             if ($state === 'today') {
                //                 $q->whereDate('created_at', \Carbon\Carbon::today());
                //             }

                //             if ($state === 'week') {
                //                 $q->whereBetween('created_at', [
                //                     \Carbon\Carbon::now()->startOfWeek(),
                //                     \Carbon\Carbon::now()->endOfWeek(),
                //                 ]);
                //             }

                //             if ($state === 'month') {
                //                 $q->whereMonth('created_at', \Carbon\Carbon::now()->month)
                //                     ->whereYear('created_at', \Carbon\Carbon::now()->year);
                //             }
                //         });
                //     })
                //     ->placeholder('Choisir période'),

            ], layout: FiltersLayout::AboveContent)
            // ->modifyQueryUsing(function (Builder $query) {
            //     $filters = request()->input('tableFilters', []);

            //     // Vérifie que 'payment_period' est défini et bien un tableau
            //     $paymentPeriod = $filters['payment_period'] ?? null;
            //     $paymentQuick  = $filters['payment_quick_period'] ?? null;

            //     // Si période manuelle (from/to) existe, on filtre dessus
            //     if (! empty($paymentPeriod['payment_from']) || ! empty($paymentPeriod['payment_until'])) {
            //         $query->whereHas('payment', function ($q) use ($paymentPeriod) {
            //             if (! empty($paymentPeriod['payment_from'])) {
            //                 $q->whereDate('created_at', '>=', $paymentPeriod['payment_from']);
            //             }
            //             if (! empty($paymentPeriod['payment_until'])) {
            //                 $q->whereDate('created_at', '<=', $paymentPeriod['payment_until']);
            //             }
            //         });
            //     }
            //     // Sinon, si période rapide choisie
            //     elseif (! empty($paymentQuick)) {
            //         $query->whereHas('payment', function ($q) use ($paymentQuick) {
            //             if ($paymentQuick === 'today') {
            //                 $q->whereDate('created_at', \Carbon\Carbon::today());
            //             }
            //             if ($paymentQuick === 'week') {
            //                 $q->whereBetween('created_at', [
            //                     \Carbon\Carbon::now()->startOfWeek(),
            //                     \Carbon\Carbon::now()->endOfWeek(),
            //                 ]);
            //             }
            //             if ($paymentQuick === 'month') {
            //                 $q->whereMonth('created_at', \Carbon\Carbon::now()->month)
            //                     ->whereYear('created_at', \Carbon\Carbon::now()->year);
            //             }
            //         });
            //     }

            //     // Sinon ➔ rien ne filtre, on affiche toutes les données
            // })

            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('stats')
                    ->label(function () {
                        return 'Nombre total de dons : ' . \App\Models\Donation::count();
                    })
                    ->disabled() // juste pour afficher
                    ->color('primary'),
                // Action::make('total_amount')
                //     ->label(fn() => 'Montant total filtré : ' . self::getTotalAmount() . ' $')
                //     ->disabled()
                //     ->color('primary'),
            ]);

    }
    protected static function paymentQuickPeriodFilter(): SelectFilter
    {
        return SelectFilter::make('payment_quick_period')
            ->label('Période rapide')
            ->options([
                'today' => "Aujourd'hui",
                'week'  => "Cette semaine",
                'month' => "Ce mois-ci",
            ])
            ->query(function (Builder $query) {
                $state = request()->input('tableFilters.payment_quick_period');
                // dd($query->toSql());
                if (! $state) {
                    return;
                }

                $query->whereHas('payments', function ($q) use ($state) {
                    if ($state === 'today') {
                        dd($q);
                        $q->whereDate('created_at', \Carbon\Carbon::today());
                    } elseif ($state === 'week') {
                        $q->whereBetween('created_at', [
                            \Carbon\Carbon::now()->startOfWeek(),
                            \Carbon\Carbon::now()->endOfWeek(),
                        ]);
                    } elseif ($state === 'month') {
                        $q->whereMonth('created_at', \Carbon\Carbon::now()->month)
                            ->whereYear('created_at', \Carbon\Carbon::now()->year);
                    }
                });
            })
            ->placeholder('Choisir période');
    }

    protected static function getTotalAmount(): string
    {
        $amount = DB::table('donations')
                         // ->join('payments', 'payments.donation_id', '=', 'donations.id')
            ->sum('amount'); // ← montant dans payment

        return number_format($amount, 2, ',', ' ');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonates::route('/'),
            // 'create' => Pages\CreateDonate::route('/create'),
            // 'edit' => Pages\EditDonate::route('/{record}/edit'),
        ];
    }
    protected static function paymentFilters(): Filter
    {
        return Filter::make('payment_filters');
        // ton code pour filtrer type et status
    }

    protected static function paymentPeriodFilter(): Filter
    {
        return Filter::make('payment_period')
            ->form([
                DatePicker::make('payment_from')->label('Date de début'),
                DatePicker::make('payment_until')->label('Date de fin'),
            ])
            ->query(function (Builder $query, array $data) {
                return $query->when(filled($data['payment_from'] ?? null) || filled($data['payment_until'] ?? null), function (Builder $query) use ($data) {
                    $query->whereHas('payments', function ($q) use ($data) {
                        if (! empty($data['payment_from'])) {
                            $q->whereDate('created_at', '>=', $data['payment_from']);
                        }
                        if (! empty($data['payment_until'])) {
                            $q->whereDate('created_at', '<=', $data['payment_until']);
                        }
                    });
                });
            });
    }

}
