<?php

namespace Space\Cloudflare\Filament\Pages;

use Cloudflare\API\Adapter\ResponseException;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Schema;
use Space\Cloudflare\Filament\CloudflarePlugin;
use Space\Cloudflare\Services\Cloudflare;

/**
 * @property Schema $form
 */
class CreateDomain extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'cloudflare::filament.pages.create-domain';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public static function getSlug(?Panel $panel = null): string
    {
        return CloudflarePlugin::get()->getSlug() . '/create';
    }

    public function getTitle(): string
    {
        return 'Add Domain';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('domain')
                    ->label('Domain Name')
                    ->placeholder('example.com')
                    ->required(),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();

        try {
            $result = app(Cloudflare::class)->addDomain($data['domain']);

            $nameservers = implode(', ', $result->name_servers ?? []);

            Notification::make()
                ->title('Domain created successfully')
                ->body("Nameservers: $nameservers")
                ->success()
                ->persistent()
                ->send();

            $this->redirect(ListDomains::getUrl());
        } catch (ResponseException $e) {
            Notification::make()
                ->title('Failed to create domain')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
