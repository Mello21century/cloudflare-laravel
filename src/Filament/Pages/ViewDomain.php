<?php

namespace Space\Cloudflare\Filament\Pages;

use Filament\Pages\Page;
use Filament\Panel;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Space\Cloudflare\Filament\CloudflarePlugin;
use Space\Cloudflare\Services\Cloudflare;

class ViewDomain extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'cloudflare::filament.pages.view-domain';

    protected static bool $shouldRegisterNavigation = false;

    public string $zoneId;

    public string $zoneName = '';

    public string $zoneStatus = '';

    public array $nameservers = [];

    public static function getSlug(?Panel $panel = null): string
    {
        return CloudflarePlugin::get()->getSlug() . '/{zoneId}';
    }

    public function getTitle(): string
    {
        return $this->zoneName ?: 'View Domain';
    }

    public function mount(string $zoneId): void
    {
        $this->zoneId = $zoneId;

        $cf = app(Cloudflare::class);
        $zone = $cf->zones->getZoneById($zoneId);

        $this->zoneName = $zone->result->name ?? '';
        $this->zoneStatus = $zone->result->status ?? '';
        $this->nameservers = $zone->result->name_servers ?? [];
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'A', 'AAAA' => 'success',
                        'CNAME' => 'info',
                        'MX' => 'warning',
                        'NS' => 'primary',
                        default => 'gray',
                    }),

                TextColumn::make('name')
                    ->label('Name')
                    ->wrap(),

                TextColumn::make('content')
                    ->label('Content')
                    ->wrap()
                    ->limit(80),

                IconColumn::make('proxied')
                    ->boolean()
                    ->label('Proxied'),

                TextColumn::make('ttl')
                    ->label('TTL')
                    ->formatStateUsing(fn($state) => $state === 1 ? 'Auto' : $state),
            ])
            ->records(function (): Collection {
                return collect(app(Cloudflare::class)->getDns($this->zoneId))
                    ->map(fn($record) => (array) $record)
                    ->keyBy('id');
            })
            ->paginated(false);
    }
}
