<?php

namespace Space\Cloudflare\Commands;

use Illuminate\Console\Command;
use Space\Cloudflare\Services\Cloudflare;

class CloudflareReplaceIpCommand extends Command
{
    protected $signature = 'cloudflare:replace-ip
        {old : Old IP (e.g. 1.2.3.4)}
        {new : New IP (e.g. 5.6.7.8)}
        {--types=A,AAAA : DNS record types to scan (comma-separated)}
        {--dry-run : Do not update, just show what would change}
        {--per-page=50 : Page size for pagination}';

    protected $description = 'Search and replace an IP in Cloudflare DNS records across all zones.';

    public function handle(): int
    {

        $oldIp = trim((string)$this->argument('old'));
        $newIp = trim((string)$this->argument('new'));
        $types = array_filter(array_map('trim', explode(',', (string)$this->option('types'))));
        $dryRun = (bool)$this->option('dry-run');
        $perPage = (int)$this->option('per-page');

        if ($oldIp === '' || $newIp === '') {
            $this->error('Old/New IP cannot be empty.');
            return self::FAILURE;
        }


        $this->info("Scanning all zones. Replace {$oldIp} -> {$newIp}");
        $this->line('Types: ' . implode(',', $types) . ' | ' . ($dryRun ? 'DRY RUN' : 'LIVE'));

        $summary = app(Cloudflare::class)->replaceIp($oldIp, $newIp, $types, $dryRun, $perPage);

        foreach ($summary['records'] as $record) {
            $this->line("Zone: {$record['zone_name']} ({$record['zone_id']})");
            $this->line("  - MATCH {$record['record_type']} {$record['record_name']} = {$record['old_ip']}");

            if ($record['updated']) {
                $this->line("    UPDATED -> {$record['new_ip']}");
            } elseif (!$dryRun) {
                $this->error("    FAILED updating {$record['record_type']} {$record['record_name']}");
            }
        }

        $this->newLine();
        $this->info("Done. Matched: {$summary['matched']} | Updated: {$summary['updated']} | Failed: {$summary['failed']}" . ($dryRun ? ' (dry-run)' : ''));

        return self::SUCCESS;
    }
}
