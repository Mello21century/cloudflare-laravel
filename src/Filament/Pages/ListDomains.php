<?php

namespace Space\Cloudflare\Filament\Pages;

use Cloudflare\API\Adapter\ResponseException;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Space\Cloudflare\Filament\CloudflarePlugin;
use Space\Cloudflare\Services\Cloudflare;

class ListDomains extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'cloudflare::filament.pages.list-domains';

    protected static bool $shouldRegisterNavigation = true;

    public static function getNavigationIcon(): ?string
    {
        return CloudflarePlugin::get()->getNavigationIcon();
    }

    public static function getNavigationGroup(): ?string
    {
        return CloudflarePlugin::get()->getNavigationGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return CloudflarePlugin::get()->getNavigationSort();
    }

    public static function getNavigationLabel(): string
    {
        return CloudflarePlugin::get()->getNavigationLabel();
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return CloudflarePlugin::get()->getSlug();
    }

    public function getTitle(): string
    {
        return CloudflarePlugin::get()->getNavigationLabel();
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Domain')
                    ->searchable(false)
                    ->sortable(false),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success',
                        'pending' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('name_servers')
                    ->label('Nameservers')
                    ->formatStateUsing(fn($state) => is_array($state) ? implode(', ', $state) : $state),
            ])
            ->filters([
                Filter::make('domain')
                    ->schema([
                        TextInput::make('name')
                            ->label('Search Domain')
                            ->placeholder('example.com'),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (blank($data['name'] ?? null)) {
                            return null;
                        }

                        return 'Domain: ' . $data['name'];
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => ViewDomain::getUrl(['zoneId' => $record['id']])),

                Action::make('edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn($record) => EditDomain::getUrl(['zoneId' => $record['id']])),

                Action::make('cpanel')
                    ->icon('heroicon-o-server-stack')
                    ->label('Add cPanel')
                    ->schema([
                        TextInput::make('ip')
                            ->label('Server IP')
                            ->required()
                            ->ipv4(),
                        TextInput::make('spf')
                            ->label('SPF Record'),
                        TextInput::make('dkim')
                            ->label('DKIM Record'),
                    ])
                    ->action(function (array $data, $record): void {
                        try {
                            app(Cloudflare::class)->setCpanel(
                                $record['id'],
                                $data['ip'],
                                $data['spf'] ?? null,
                                $data['dkim'] ?? null
                            );

                            Notification::make()
                                ->title('cPanel DNS records added successfully.')
                                ->success()
                                ->send();
                        } catch (ResponseException $e) {
                            Notification::make()
                                ->title('Failed to add cPanel records')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('cleanDns')
                    ->icon('heroicon-o-trash')
                    ->label('Clear DNS')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        try {
                            app(Cloudflare::class)->cleanDns($record['id']);

                            Notification::make()
                                ->title('All DNS records cleared.')
                                ->success()
                                ->send();
                        } catch (ResponseException $e) {
                            Notification::make()
                                ->title('Failed to clear DNS records')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Add Domain')
                    ->icon('heroicon-o-plus')
                    ->url(CreateDomain::getUrl()),
            ])
            ->paginated(false);
    }

    public function getTableRecords(): Collection
    {
        $searchName = $this->tableFilters['domain']['name'] ?? null;

        $domains = app(Cloudflare::class)->getDomains(
            name: blank($searchName) ? null : $searchName
        );
        $domains = collect($domains)->map(function ($domain) {
            $domain->__key = $domain->id ?? '';
            return (array)$domain;
        })->toArray();

        return collect($domains ?? []);
    }

    public function getTableRecordKey($record): string
    {
        return $record['id'] ?? '';
    }
}
