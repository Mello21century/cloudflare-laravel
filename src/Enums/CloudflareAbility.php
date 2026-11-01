<?php

namespace Space\Cloudflare\Enums;

/**
 * One permission per panel action, named like Filament Shield resource permissions.
 */
enum CloudflareAbility: string
{
    case AddDomain = 'AddDomain:Cloudflare';
    case ViewDomain = 'ViewDomain:Cloudflare';
    case EditDns = 'EditDns:Cloudflare';
    case AddDnsRecord = 'AddDnsRecord:Cloudflare';
    case AddCpanel = 'AddCpanel:Cloudflare';
    case ClearDns = 'ClearDns:Cloudflare';
    case ReplaceIp = 'ReplaceIp:Cloudflare';

    public function label(): string
    {
        return match ($this) {
            self::AddDomain => 'Cloudflare: Add Domain',
            self::ViewDomain => 'Cloudflare: View Domain',
            self::EditDns => 'Cloudflare: Edit Domain DNS',
            self::AddDnsRecord => 'Cloudflare: Add DNS Record',
            self::AddCpanel => 'Cloudflare: Add cPanel',
            self::ClearDns => 'Cloudflare: Clear DNS',
            self::ReplaceIp => 'Cloudflare: Replace IP',
        };
    }

    /**
     * @return array<string, string> Permission name => label.
     */
    public static function labels(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $ability): array => [$ability->value => $ability->label()])
            ->all();
    }
}
