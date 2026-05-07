<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Models\News;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationLabel = 'Haberler';
    protected static ?string $modelLabel = 'Haber';
    protected static ?string $pluralModelLabel = 'Haberler';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Başlık')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                Forms\Components\TextInput::make('slug')
                    ->label('URL (Slug)')
                    ->required()
                    ->unique(News::class, 'slug', ignoreRecord: true),
                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->required(),
                Forms\Components\Textarea::make('excerpt')
                    ->label('Özet')
                    ->required()
                    ->maxLength(300)
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('content')
                    ->label('İçerik')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('thumbnail')
                    ->label('Kapak Görseli')
                    ->image()
                    ->directory('news')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('thumbnail_caption')
                    ->label('Görsel Alt Yazısı')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_published')
                    ->label('Yayında mı?')
                    ->default(false),
                Forms\Components\Toggle::make('is_breaking')
                    ->label('Son Dakika mı?')
                    ->default(false),
                Forms\Components\DateTimePicker::make('published_at')
                    ->label('Yayın Tarihi'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Gündem' => 'danger',
                        'Spor' => 'success',
                        'Ekonomi' => 'warning',
                        'Teknoloji' => 'info',
                        default => 'primary',
                    }),
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Yazar')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Yayında')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_breaking')
                    ->label('Son Dakika')
                    ->boolean(),
                Tables\Columns\TextColumn::make('view_count')
                    ->label('Görüntülenme')
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Yayın Tarihi')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->label('Kategori'),
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Yayın Durumu'),
                Tables\Filters\TernaryFilter::make('is_breaking')
                    ->label('Son Dakika'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (News $record): string => route('news.show', ['slug' => $record->slug])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Toplu Yayınla')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->each->update(['is_published' => true, 'published_at' => now()]))
                        ->requiresConfirmation(),
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
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }
}
