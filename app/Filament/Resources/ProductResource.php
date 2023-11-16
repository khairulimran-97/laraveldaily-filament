<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $recordTitleAttribute = 'name';
    protected static int $globalSearchResultsLimit = 3;

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return self::getUrl('view', ['record' => $record]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }
    protected static ?int $navigationSort = 2;

    protected static array $statuses = [
        'in stock' => 'in stock',
        'sold out' => 'sold out',
        'coming soon' => 'coming soon',
    ];

    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\Section::make('Main data')
                // ->description('What users totally need to fill in')
                Forms\Components\Wizard::make([
                Forms\Components\Wizard\Step::make('Main data')
            ->schema([
                Forms\Components\TextInput::make('name')
                ->label(__('Product name'))
                ->required()
                ->unique(ignoreRecord: true)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', str()->slug($state))),
                Forms\Components\TextInput::make('slug')
                ->visibleOn('create')
                ->required(),
                Forms\Components\TextInput::make('price')
                ->required()
                ->rule('numeric'),
            ]),
                // Forms\Components\Section::make('Additional data')
                Forms\Components\Wizard\Step::make('Additional data')
            ->schema([
                Forms\Components\Radio::make('status')
                ->options(self::$statuses),
                Forms\Components\Select::make('category_id')
                ->relationship('category', 'name'),
                Forms\Components\RichEditor::make('description')
                ->columnSpanFull()
                ->required(),
            ]),
            ])

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextInputColumn::make('name')
                ->rules(['required', 'min:3']),
                //->searchable(isIndividual: true, isGlobal: false),
                Tables\Columns\TextColumn::make('price')
                ->sortable()
                //->searchable(isIndividual: true, isGlobal: false)
                ->money('myr')
                ->getStateUsing(function (Product $record): float {
                return $record->price / 100;
                })
                ->alignCenter(),
                Tables\Columns\ToggleColumn::make('is_active')
                ->onColor('success') // default value: "primary"
                ->offColor('danger'),
                Tables\Columns\SelectColumn::make('status')
                ->options(self::$statuses),
                Tables\Columns\TextColumn::make('category.name')
                ->label('Category name'),
                Tables\Columns\TextColumn::make('tags.name')->badge(),
                Tables\Columns\TextColumn::make('created_at')
                ->since(),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(self::$statuses),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\Filter::make('created_from')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('created_until')
                    ->form([
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TagsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
           'edit' => Pages\EditProduct::route('/{record}/edit'),
           'view' => Pages\ViewProduct::route('/{record}'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('name'),
                TextEntry::make('price'),
                TextEntry::make('is_active'),
                TextEntry::make('status'),
            ]);
    }
}
