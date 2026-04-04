<?php

namespace Space\Cloudflare\Filament\Pages;

use Cloudflare\API\Adapter\ResponseException;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Space\Cloudflare\Enums\DnsType;
use Space\Cloudflare\Filament\CloudflarePlugin;
use Space\Cloudflare\Services\Cloudflare;

class EditDomain extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;

    protected string $view = 'cloudflare::filament.pages.edit-domain';

    protected static bool $shouldRegisterNavigation = false;

    public string $zoneId;

    public string $zoneName = '';

    public static function getSlug(?Panel $panel = null): string
    {
        return CloudflarePlugin::get()->getSlug() . '/{zoneId}/edit';
    }

    public function getTitle(): string
    {
        return $this->zoneName ? "Edit DNS - $this->zoneName" : 'Edit DNS Records';
    }

    public function mount(string $zoneId): void
    {
        $this->zoneId = $zoneId;

        $cf = app(Cloudflare::class);
        $zone = $cf->zones->getZoneById($zoneId);
        $this->zoneName = $zone->result->name ?? '';
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
            ->recordActions([
                Action::make('editRecord')
                    ->icon('heroicon-o-pencil')
                    ->label('Edit')
                    ->fillForm(function ($record) {
                        return [
                            'name' => $record['name'],
                            'type' => $record['type'],
                            'content' => $record['content'],
                            'proxied' => $record['proxied'] ?? false,
                        ];
                    })
                    ->schema($this->dnsRecordForm())
                    ->action(function (array $data, $record): void {
                        try {
                            app(Cloudflare::class)->updateDns(
                                zoneId: $this->zoneId,
                                recordId: $record['id'],
                                name: $data['name'],
                                content: $data['content'],
                                type: $data['type'],
                                proxied: $data['proxied'] ?? false,
                            );

                            Notification::make()
                                ->title('DNS record updated.')
                                ->success()
                                ->send();
                        } catch (ResponseException $e) {
                            Notification::make()
                                ->title('Failed to update DNS record')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('deleteRecord')
                    ->icon('heroicon-o-trash')
                    ->label('Delete')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        try {
                            app(Cloudflare::class)->deleteDns(
                                zoneId: $this->zoneId,
                                recordId: $record['id'],
                            );

                            Notification::make()
                                ->title('DNS record deleted.')
                                ->success()
                                ->send();
                        } catch (ResponseException $e) {
                            Notification::make()
                                ->title('Failed to delete DNS record')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->headerActions([
                Action::make('addRecord')
                    ->label('Add DNS Record')
                    ->icon('heroicon-o-plus')
                    ->schema($this->dnsRecordForm())
                    ->action(function (array $data): void {
                        try {
                            app(Cloudflare::class)->setDns(
                                zoneId: $this->zoneId,
                                name: $data['name'],
                                content: $data['content'],
                                type: $data['type'],
                                proxied: $data['proxied'] ?? false,
                            );

                            Notification::make()
                                ->title('DNS record added.')
                                ->success()
                                ->send();
                        } catch (ResponseException $e) {
                            Notification::make()
                                ->title('Failed to add DNS record')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->paginated(false);
    }

    protected function dnsRecordForm(): array
    {
        return [
            TextInput::make('name')
                ->label('Name')
                ->required(),

            Select::make('type')
                ->label('Type')
                ->options(
                    collect(DnsType::cases())->mapWithKeys(fn(DnsType $type) => [$type->value => $type->value])
                )
                ->required(),

            Textarea::make('content')
                ->label('Content')
                ->required(),

            Toggle::make('proxied')
                ->label('Proxied')
                ->default(false),
        ];
    }

    public function getTableRecords(): Collection
    {
        $data = collect(app(Cloudflare::class)->getDns($this->zoneId))->map(function ($record) {
            $record->__key = $record->id ?? '';
            return (array)$record;
        })->toArray();
        return collect($data ?? []);
    }

    public function getTableRecordKey($record): string
    {
        return $record['id'];
    }
}
