<?php

namespace Space\Cloudflare\Filament\Pages;

use Cloudflare\API\Adapter\ResponseException;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                    ->formatStateUsing(fn($state) => is_array($state) ? implode('<br> ', $state) : $state),
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
                Action::make('replaceIp')
                    ->label('Replace IP')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->schema([
                        TextInput::make('old_ip')
                            ->label('Old IP')
                            ->required()
                            ->rules(['ip']),
                        TextInput::make('new_ip')
                            ->label('New IP')
                            ->required()
                            ->rules(['ip']),
                        TextInput::make('types')
                            ->label('Record types')
                            ->default('A,AAAA')
                            ->helperText('Comma-separated Cloudflare DNS record types to scan.')
                            ->required(),
                        Toggle::make('dry_run')
                            ->label('Dry run only')
                            ->default(true)
                            ->helperText('Preview matching records without changing Cloudflare.'),
                    ])
                    ->requiresConfirmation()
                    ->modalHeading('Replace DNS record IPs')
                    ->modalDescription('This scans all Cloudflare zones and replaces records whose content exactly matches the old IP.')
                    ->action(function (array $data): void {
                        try {
                            $types = array_filter(array_map('trim', explode(',', (string) ($data['types'] ?? 'A,AAAA'))));
                            $summary = app(Cloudflare::class)->replaceIp(
                                $data['old_ip'],
                                $data['new_ip'],
                                $types,
                                (bool) ($data['dry_run'] ?? true)
                            );

                            Notification::make()
                                ->title(($data['dry_run'] ?? true) ? 'IP replacement dry run completed.' : 'IP replacement completed.')
                                ->body("Matched: {$summary['matched']} | Updated: {$summary['updated']} | Failed: {$summary['failed']}")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Failed to replace IP')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('create')
                    ->label('Add Domain')
                    ->icon('heroicon-o-plus')
                    ->url(CreateDomain::getUrl()),
            ])
            ->records(function (?array $filters): Collection {
                $searchName = $filters['domain']['name'] ?? null;

                return collect(app(Cloudflare::class)->getDomains(
                    name: blank($searchName) ? null : $searchName
                ))
                    ->map(fn($domain) => (array) $domain)
                    ->keyBy('id');
            })
            ->paginated(false);
    }
}
