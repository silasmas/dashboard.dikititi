<?php
namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Category;
use App\Models\Media;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action as tab;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\App;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;

    protected static ?string $navigationIcon       = 'heroicon-o-film';
    protected static ?string $recordTitleAttribute = 'media_title';
    protected static ?int $navigationSort          = 1;

    public static function form(Form $form): Form
    {
        $id = resolveMediaId($form);

        return $form
            ->schema([
                Wizard::make([
                    Step::make('Étape 1 ')
                        ->schema([
                            Section::make('Information générale')->schema([
                                TextInput::make('media_title')
                                    ->label('Titre du media')
                                    ->columnSpan(4)
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('belonging_count')
                                    ->label('Nombre des contenants')
                                    ->columnSpan(4)
                                    ->numeric()
                                    ->maxLength(255),
                                TextInput::make('source')
                                    ->label('Source')
                                    ->columnSpan(4)
                                    ->required()
                                    ->maxLength(255),
                                TimePicker::make('time_length')
                                    ->label('Temps du media')
                                    ->seconds(false)
                                    ->prefixIcon('heroicon-m-play')
                                    ->columnSpan(4),
                                TextInput::make('author_names')
                                    ->label('Auteur')
                                    ->columnSpan(4)
                                    ->maxLength(255),
                                TextInput::make(name: 'director')
                                    ->label('Réalisateur')
                                    ->columnSpan(4)
                                    ->maxLength(255),
                                TextInput::make('writer')
                                    ->label('Ecrit par :')
                                    ->columnSpan(6)
                                    ->maxLength(255),
                                TextInput::make('artist_names')
                                    ->label('Nom de l\'artiste')
                                    ->columnSpan(6)
                                    ->maxLength(255),
                                TextInput::make('teaser_url')
                                    ->label('Teaser URL')
                                    ->prefix('https://')
                                    ->columnSpan(6),
                                Textarea::make('media_description')
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                                Toggle::make('is_public')
                                    ->label('Active (pour le rendre visible ou pas)')
                                    ->columnSpanFull()
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->required(),
                            ])->columns(12),
                        ]),
                    Step::make('Étape 2')->schema([
                        Section::make('Information générale')->schema([
                            Select::make('for_youth')
                                ->options([
                                    '1' => 'OUI',
                                    '0' => 'NON',
                                ])
                                ->label("Pour enfant ?")
                                ->searchable()->columnSpan(6),
                            Select::make('is_live')
                                ->options([
                                    '1' => 'OUI',
                                    '0' => 'NON',
                                ])
                                ->label("Est un live?")
                                ->searchable()->columnSpan(6),
                            // Select::make('belongs_to')
                            //     ->label('Appartien à :')
                            //     ->searchable()
                            //     ->preload()
                            //     ->columnSpan(6),
                            Select::make('belongs_to')
                                ->label('Appartien à :')
                                ->searchable()
                                ->preload()
                                ->columnSpan(6)
                                ->options(function () {
                                    $locale = app()->getLocale();

                                    return \App\Models\Type::all()
                                        ->filter(fn($type) => ! empty($type->type_name[$locale])) // ignore les valeurs nulles ou vides
                                        ->pluck("type_name.$locale", 'id')
                                        ->toArray();
                                }),

                            Select::make('type_id')
                                ->label('Type :')
                                ->searchable()
                                ->preload()
                                ->relationship('type', 'id') // on utilise 'id' ici car l’affichage est personnalisé via options()
                                ->options(function () {
                                    $locale    = app()->getLocale();
                                    $groupName = 'Type de média'; // ou ce que tu veux filtrer

                                    return \App\Models\Type::whereHas('group', function ($query) use ($locale, $groupName) {
                                        $query->where("group_name->{$locale}", $groupName);
                                    })
                                        ->get()
                                        ->mapWithKeys(function ($type) use ($locale) {
                                            return [
                                                $type->id => $type->type_name ?? '[Nom non défini]',
                                            ];
                                        })
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->placeholder('Sélectionnez un type')
                                ->helperText('Les types disponibles sont filtrés selon la langue')

                                ->columnSpan(6)
                                ->required(),

                            CheckboxList::make('categories')
                                ->label('Choisissez au moins une catégorie')
                                ->relationship('categories', 'category_name') // <-- le nom de la relation (pas category_id !)
                                ->options(
                                    \App\Models\Category::all()
                                        ->mapWithKeys(function ($category) {
                                            // $locale = App::getLocale();
                                            // $name = is_array($category->category_name)
                                            //     ? ($category->category_name[$locale] ?? '[Sans nom]')
                                            //     : $category->category_name;

                                            // return [$category->id => $name];
                                            if (is_null($category)) {
                                                return []; // Ou affiche un message par défaut
                                            }
                                            return [$category->id => $category->category_name];
                                        })
                                        ->toArray()
                                )
                                ->searchable()
                                ->columns([
                                    'sm' => 2,
                                    'md' => 3,
                                    'lg' => 4,
                                ])
                                ->required()
                                ->columnSpanFull()

                        ])->columns(12),
                    ]),
                    Step::make('Étape 3')->schema([
                        Section::make('Upload des couvertures')->schema([
                            FileUpload::make('cover_url')
                                ->label('Couverture')
                                ->directory('images/medias/' . $id . '/cover') // ✅ bon chemin relatif
                                ->disk('public')                               // ✅ très important
                                ->imageEditor()
                                ->imageEditorMode(2)
                                ->downloadable()
                                ->visibility('public') // ou 'private' si tu gères l'accès via contrôleur
                                ->image()
                                ->maxSize(3024)
                                ->columnSpan(6)
                                ->previewable(true),

                            FileUpload::make('thumbnail_url')
                                ->label('Couverture en miniature')
                                ->directory('/images/medias/' . $id . '/thumbnail')
                                ->imageEditor()
                                ->disk('public')
                                ->imageEditorMode(2)
                                ->downloadable()
                                ->visibility('public') // ou 'private' si tu gères l'accès via contrôleur
                                ->image()
                                ->maxSize(3024)
                                ->columnSpan(6)
                                ->previewable(true),
                        ]),
                    ]),
                    Step::make('Étape 4')->schema([
                        Section::make('Vidéo')->schema([
                            \Filament\Forms\Components\View::make('livewire.upload-video-chunked')
                                ->columnSpan(12),
                            TextInput::make('media_url')
                                ->id('media_url_filament')
                                ->label('Lien de la vidéo')
                                ->disabled()       // Lecture seule
                                ->dehydrated(true) // Important pour l'enregistrement
                                ->afterStateHydrated(fn($component, $state) => $component->state($state))
                                ->helperText('Ce lien est généré automatiquement après upload. Cliquez sur 🔗 pour l’ouvrir dans un nouvel onglet.')
                                ->columnSpan(12)
                                ->suffixActions([
                                    Action::make('ouvrir')
                                        ->icon('heroicon-o-arrow-top-right-on-square')
                                        ->tooltip('Ouvrir la vidéo dans un nouvel onglet')
                                        ->url(fn($state) => $state)
                                        ->openUrlInNewTab()
                                        ->visible(fn($state) => filled($state)),
                                ]),

                        ])->columns(12),
                    ]),

                ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                ImageColumn::make('cover_url')
                    ->label("Couverture")
                    ->defaultImageUrl(url('assets/images/avatars/default.jpg')),
                ImageColumn::make('thumbnail_url')
                    ->label("Miniature")
                    ->defaultImageUrl(url('assets/images/avatars/default.jpg')),
                TextColumn::make('media_title')
                    ->label('Titre')
                    ->limit(20)
                    ->searchable(),
                TextColumn::make('source')
                    ->label('Source')
                    ->searchable(),
                IconColumn::make('is_public')
                    ->label('Est active')
                    ->boolean(),
                TextColumn::make('time_length')
                    ->label('Temps du media')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('author_names')
                    ->label('Auteur')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('artist_names')
                    ->label('Artiste')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('writer')
                    ->label('Ecrit par ')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('director')
                    ->label('Réalisateur')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                TextColumn::make('published_date')
                    ->date()
                    ->since()
                    ->label('Date de publication')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                IconColumn::make('for_youth')
                    ->label('Pour enfant?')
                    ->boolean(),
                IconColumn::make('is_live')
                    ->label('Est un live?')
                    ->boolean(),
                TextColumn::make('belongs_to')
                    ->label('Type')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->numeric()
                    ->sortable(),
                TextColumn::make('media_url')
                    ->label('Action')
                    ->formatStateUsing(fn($state) => '<a href="' . $state . '" target="_blank" class="text-primary underline">🎬 Lire</a>')
                    ->html(),

                TextColumn::make('created_at')
                    ->label('Date de création')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Date de mis à jour')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_live')
                    ->label('En live')
                    ->options([
                        '1' => 'Oui',
                        '0' => 'Non',
                    ]),
                SelectFilter::make('for_youth')
                    ->label('Pour enfant ')
                    ->options([
                        '1' => 'Oui',
                        '0' => 'Non',
                    ]),
                SelectFilter::make('artist_names')
                    ->label('Artiste')
                    ->options(Media::whereNotNull('artist_names')
                            ->select('artist_names')
                            ->distinct()
                            ->pluck('artist_names', 'artist_names')
                            ->toArray()
                    ),
                SelectFilter::make('category_id')
                    ->label('Catégorie')
                    ->options(Category::select('category_name')->get()->map(function ($category) {
                        // return [$category->id => $category->category_name?? ''];
                        // Assurez-vous que category_name est bien une chaîne
                        $name = is_array($category->category_name) ? ($category->category_name['fr'] ?? '') : $category->category_name;
                        // dd([$category->id => $name]);

                        return [$category->id => $name];
                    })->toArray()),

                SelectFilter::make('source')
                    ->label('Source')
                    ->options(
                        ['' => 'Toutes les sources'] + Media::whereNotNull('source')
                            ->select('source')
                            ->distinct()
                            ->pluck('source', 'source')
                            ->toArray()
                    ),

                // Dans votre classe de ressource

            ], layout: FiltersLayout::AboveContent)
            ->searchable() // ✅ active la recherche globale
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->headerActions([
                tab::make('statistiques')
                    ->label(fn() => '📊 ' . \App\Models\Media::count() . ' Media au total')
                    ->disabled() // juste pour l'afficher
                    ->color('gray'),

                tab::make('export-tout')
                    ->label('Exporter tout')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->action(function () {
                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\MediasExport(\App\Models\Media::all()),
                            'allMedia-toutes.xlsx'
                        );
                    }),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // public static function getActions(): array
    // {
    //     return [
    //         Action::make('Vue en Grille')
    //             ->url(route('filament.admin.resources.media.gallery'))
    //             ->icon('heroicon-o-view-columns'),
    //     ];
    // }
    public static function getLabel(): string
    {
        return 'Galerie';
    }

    public static function getNavigationLabel(): string
    {
        return 'Media';
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'edit'   => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function getNavigationBadgeColor(): string | array | null
    {
        return "info";
    }

    // public static function getGloballySearchableAttributes(): array
    // {
    //     return ['media_title', 'source', 'writer'];
    // }
}
