<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?int $navigationSort = 1;
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return Order::whereDate('created_at', today())->count() ? 'NEW' : '';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                ->dateTime(),
            Tables\Columns\TextColumn::make('product.name'),
            Tables\Columns\TextColumn::make('user.name'),
            Tables\Columns\TextColumn::make('price')
                ->money('MYR')
                ->getStateUsing(function (Order $record): float {
                    return $record->price / 100;
                })
                ->summarize(Tables\Columns\Summarizers\Sum::make()
                ->formatStateUsing(fn ($state) => 'RM ' . number_format($state / 100, 2))
            ),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultGroup('product.name')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Mark Completed')
                ->requiresConfirmation()
                ->icon('heroicon-o-check-badge')
                ->hidden(fn (Order $record) => $record->is_completed)
                ->action(fn (Order $record) => $record->update(['is_completed' => true])),
                ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('Mark as Completed')
                    ->icon('heroicon-o-check-badge')
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->update(['is_completed' => true]))
                    ->deselectRecordsAfterCompletion()
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('New Order')
                    ->url(fn (): string => OrderResource::getUrl('create')),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
