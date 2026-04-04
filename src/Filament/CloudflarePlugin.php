<?php

namespace Space\Cloudflare\Filament;

use Filament\Contracts\Plugin;
use Filament\FilamentManager;
use Filament\Panel;
use Space\Cloudflare\Filament\Pages\CreateDomain;
use Space\Cloudflare\Filament\Pages\EditDomain;
use Space\Cloudflare\Filament\Pages\ListDomains;
use Space\Cloudflare\Filament\Pages\ViewDomain;

class CloudflarePlugin implements Plugin
{
    protected string $navigationIcon = 'heroicon-o-cloud';

    protected ?string $navigationGroup = null;

    protected ?int $navigationSort = null;

    protected string $slug = 'cloudflare';

    protected string $navigationLabel = 'Cloudflare';

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): Plugin|FilamentManager
    {
        return filament(app(static::class)->getId());
    }

    public function getId(): string
    {
        return 'cloudflare';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([
            ListDomains::class,
            CreateDomain::class,
            ViewDomain::class,
            EditDomain::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function navigationIcon(string $icon): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function getNavigationIcon(): string
    {
        return $this->navigationIcon;
    }

    public function navigationGroup(?string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function getNavigationGroup(): ?string
    {
        return $this->navigationGroup;
    }

    public function navigationSort(?int $sort): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function getNavigationSort(): ?int
    {
        return $this->navigationSort;
    }

    public function slug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function navigationLabel(string $label): static
    {
        $this->navigationLabel = $label;

        return $this;
    }

    public function getNavigationLabel(): string
    {
        return $this->navigationLabel;
    }
}
