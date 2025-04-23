<?php
namespace App\Filament\Resources;

use App\Filament\Resources\StatusResource\Pages;
use App\Models\Group;
use App\Models\Status;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\App;

class StatusResource extends Resource
{

    protected static ?string $model           = Status::class;
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?string $navigationIcon  = 'heroicon-o-check-circle';
    public static function getDefaultTranslatableLocale(): string
    {
        return App::getLocale(); // ou 'fr'
    }

    public static function getTranslatableLocales(): array
    {
        return ['fr', 'en', 'ln'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('group_id')
                    ->relationship('group', 'group_name')
                    ->searchable()
                    ->options(Group::all()->pluck('group_name', 'id'))
                    ->preload()
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('status_name.fr')
                    ->maxLength(65535)
                    ->columnSpan(4)
                    ->label('Nom (Français)'),
                TextInput::make('status_name.en')
                    ->columnSpan(4)
                    ->label('Nom (Anglais)'),
                TextInput::make('status_name.ln')
                    ->columnSpan(4)
                    ->label('Nom (Lingala)'),
                TextInput::make('icon')
                    ->columnSpan(6)
                    ->maxLength(45),
                // TextInput::make('color')
                //     ->columnSpan(6)
                //     ->helperText('success, warning, danger, info, primary, secondary')
                //     ->label('Couleur')
                //     ->maxLength(45),
                Select::make('color')
                    ->label('Couleur Bootstrap')
                    ->options([
                        'primary'   => 'Bleu principal (primary)',
                        'secondary' => 'Gris secondaire (secondary)',
                        'success'   => 'Vert succès (success)',
                        'warning'   => 'Jaune avertissement (warning)',
                        'danger'    => 'Rouge erreur (danger)',
                        'info'      => 'Bleu clair info (info)',
                    ])
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpan(6),

                // ColorPicker::make('color')
                //     ->label('Couleur')
                //     ->required()
                //     ->columnSpan(6)
                //     ->rule('regex:/^#[0-9A-Fa-f]{6}$/')
                //     ->dehydrateStateUsing(fn($state) => $state), // garde juste le code hex

                Textarea::make('status_description')
                    ->columnSpanFull(),
            ])->columnS(12);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status_name')
                    ->label('Nom')
                    ->searchable(),
                TextColumn::make('group.group_name')
                    ->label('Groupe')
                    ->searchable(),
                TextColumn::make('status_description')
                    ->label('Description')
                    ->searchable(),
                TextColumn::make('icon')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('color')
                    ->label('Couleur')
                    ->badge()
                    ->color(fn(string $state) => $state) // Utilise le nom Bootstrap pour la couleur
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'primary'                            => 'Primary',
                        'secondary'                          => 'Secondary',
                        'success'                            => 'Success',
                        'warning'                            => 'Warning',
                        'danger'                             => 'Danger',
                        'info'                               => 'Info',
                        default                              => ucfirst($state),
                    }),
                // TextColumn::make('color')
                //     ->toggleable(isToggledHiddenByDefault: true)
                //     ->searchable(),
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
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListStatuses::route('/'),
            // 'create' => Pages\CreateStatus::route('/create'),
            // 'edit' => Pages\EditStatus::route('/{record}/edit'),
        ];
    }

}
