<?php

namespace Space\Cloudflare\Commands;

use Illuminate\Console\Command;
use Cloudflare\API\Auth\APIToken;
use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Endpoints\Zones;
use Cloudflare\API\Endpoints\DNS;

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


        $cloudflare = app('cloudflare');

        $adapter = $cloudflare->adapter;
        $zonesEndpoint = $cloudflare->zones;
        $dnsEndpoint = $cloudflare->dns;

        $this->info("Scanning all zones. Replace {$oldIp} -> {$newIp}");
        $this->line('Types: ' . implode(',', $types) . ' | ' . ($dryRun ? 'DRY RUN' : 'LIVE'));

        $page = 1;
        $totalUpdated = 0;
        $totalMatched = 0;

        while (true) {
            // SDK Zones endpoint doesn't always expose pagination nicely across versions,
            // but listZones() accepts page/perPage in many releases.
            $zones = $zonesEndpoint->listZones(page: $page, perPage: $perPage);

            if (empty($zones->result)) {
                break;
            }

            foreach ($zones->result as $zone) {
                $zoneId = $zone->id ?? null;
                $zoneName = $zone->name ?? '(unknown)';

                if (!$zoneId) {
                    continue;
                }


                $this->info("Zone: {$zoneName} ({$zoneId})");

                foreach ($types as $type) {
                    $dnsPage = 1;

                    while (true) {
                        // listRecords($zoneId, $type, $name, $content, $page, $perPage, $order, $direction, $match)
                        $records = $dnsEndpoint->listRecords($zoneId, $type, '', $oldIp, $dnsPage, $perPage);

                        if (empty($records->result)) {
                            break;
                        }

                        foreach ($records->result as $r) {
                            $recordId = $r->id ?? null;
                            $recordName = $r->name ?? '';
                            $recordType = $r->type ?? '';
                            $recordContent = $r->content ?? '';

                            if (!$recordId) {
                                continue;
                            }

                            // Safety check: ensure its EXACT match to old IP
                            if (trim($recordContent) !== $oldIp) {
                                continue;
                            }

                            $totalMatched++;
                            $this->line("  - MATCH {$recordType} {$recordName} = {$recordContent}");

                            if ($dryRun) {
                                continue;
                            }

                            // Build an updated record payload.
                            // Keep proxied/ttl if present.
                            $ttl = $r->ttl ?? 1;
                            $proxied = property_exists($r, 'proxied') ? (bool)$r->proxied : null;

                            $ok = $dnsEndpoint->updateRecordDetails(
                                $zoneId,
                                $recordId,
                                [
                                    'type' => $recordType,
                                    'name' => $recordName,
                                    'content' => $newIp,
                                    'ttl' => $ttl,
                                    // only send proxied if known (some types don’t have it)
                                    ...($proxied !== null ? ['proxied' => $proxied] : []),
                                ]
                            );

                            if ($ok) {
                                $totalUpdated++;
                                $this->line("    UPDATED -> {$newIp}");
                            } else {
                                $this->error("    FAILED updating {$recordType} {$recordName}");
                            }
                        }

                        $dnsPage++;
                    }
                }
            }

            $page++;
        }

        $this->newLine();
        $this->info("Done. Matched: {$totalMatched} | Updated: {$totalUpdated}" . ($dryRun ? ' (dry-run)' : ''));

        return self::SUCCESS;
    }
}
