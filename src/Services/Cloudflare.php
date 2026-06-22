<?php

namespace Space\Cloudflare\Services;

use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\Zones;
use stdClass;

class Cloudflare
{

    protected APIKey $key;
    public Guzzle $adapter;
    public DNS $dns;
    public Zones $zones;

    /**
     * Create a new class instance.
     */
    public function __construct(private readonly string $email, private readonly string $apiKey)
    {
        $this->key = new APIKey($this->email, $this->apiKey);
        $this->adapter = new Guzzle($this->key);
        $this->dns = new DNS($this->adapter);
        $this->zones = new Zones($this->adapter);
    }

    public function getDomains($perPage = 50, ?string $name = null): ?array
    {
        return $this->zones->listZones(name: $name ?? '', perPage: $perPage)?->result;
    }

    public function addDomain($domain): stdClass
    {
        return $this->zones->addZone(name: $domain);
    }

    public function getDns(string $zoneId, $perPage = 50): ?array
    {
        return $this->dns->listRecords(zoneID: $zoneId, perPage: $perPage)?->result;
    }

    public function setDns(string $zoneId, string $name, string $content, string $type = 'A', string $priority = '', bool $proxied = false): bool
    {
        return $this->dns->addRecord($zoneId, $type, $name, $content, proxied: $proxied, priority: $priority);
    }

    public function deleteDns(string $zoneId,string $recordId): string
    {
        return $this->dns->deleteRecord(zoneID: $zoneId, recordID: $recordId);
    }

    public function updateDns(string $zoneId, string $recordId, string $name, string $content, string $type = 'A', bool $proxied = false): stdClass
    {
        try {

            $return = $this->dns->updateRecordDetails(zoneID: $zoneId, recordID: $recordId, details: compact('name', 'content', 'type', 'proxied'));
        } catch (\Exception $e) {
            dd($e);
        }
        return $return;
    }

    public function setCpanel(string $zoneId, string $ip, ?string $spf = null, ?string $dkim = null): void
    {
        $posts = [
            [
                'name' => '@',
                'content' => $ip,
                'proxied' => false,
                'type' => 'A'
            ],
            [
                'name' => 'www',
                'content' => $ip,
                'proxied' => false,
                'type' => 'A'
            ],
            [
                'name' => 'mail',
                'content' => $ip,
                'proxied' => false,
                'type' => 'A'
            ],
            [
                'name' => 'cpcalendars',
                'content' => $ip,
                'proxied' => false,
                'type' => 'A'
            ],
            [
                'name' => 'autodiscover',
                'content' => $ip,
                'proxied' => false,
                'type' => 'A'
            ],
            [
                'name' => 'cpcontacts',
                'content' => $ip,
                'proxied' => false,
                'type' => 'A'
            ],
            [
                'name' => 'whm',
                'content' => $ip,
                'proxied' => false,
                'type' => 'A'
            ],
            [
                'name' => 'webdisk',
                'content' => $ip,
                'proxied' => false,
                'type' => 'A'
            ],
            [
                'name' => 'autoconfig',
                'content' => $ip,
                'proxied' => false,
                'type' => 'A'
            ],
            [
                'name' => 'cpanel',
                'content' => $ip,
                'proxied' => false,
                'type' => 'A'
            ],
            [
                'name' => 'webmail',
                'content' => $ip,
                'proxied' => false,
                'type' => 'A'
            ],
        ];
        if (!is_null($spf))
            $posts[] = [
                'name' => '@',
                'content' => '"' . $spf . '"',
                'type' => 'TXT'
            ];
        else
            $posts[] = [
                'name' => '@',
                'content' => '"v=spf1 ip4:' . $ip . ' +a +mx -all"',
                'type' => 'TXT'
            ];
        if (!is_null($dkim))
            $posts[] = [
                'name' => 'default._domainkey',
                'content' => '"' . $dkim . '"',
                'type' => 'TXT'
            ];
        else
            $posts[] = [
                'name' => 'default._domainkey',
                'content' => '"v=DKIM1; k=rsa; p=MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAo9/zl6x6PKuAYEpno0W5VO6z3W6AoiP0Oi4V0XBW98MU9eZ8Qb3+gwHp5XAe1eSzHuYCvRTbvfRsTkDVEisC2dH/TPahpzZGhhXIznT36WT2z5OtCvHjvIYTtg1o1uru4TaKkCjkmSbXeAG9fuvodNRDYNeag5L8mRlhGc5ROCT88YV4RN0lqSgnLDcwo/Pi4" "\010Kd2qKFF3pbk+lKsRAcXEgmKoJSNv7GicruQ7v04uF/1sQhGvrhv86z83AS930ClAJ0eQGG87PGOOU+sVute7qRhgBocMtLlEQWg4S6d20W9GTfVvNVAfqvjGz9DbbdIPfIXnbtXolLmFrRTH1eiSQIDAQAB;"',
                'type' => 'TXT'
            ];
        $this->dns->batchRecords(zoneID: $zoneId, posts: $posts);
    }

    public function cleanDns(string $zoneId): bool
    {
        $records = $this->getDns($zoneId, 200);
        $ids = collect($records)->pluck('id')->map(fn($id) => ['id' => $id]);
        return $this->dns->batchRecords($zoneId, $ids->toArray());
    }

    public function replaceIp(string $oldIp, string $newIp, array $types = ['A', 'AAAA'], bool $dryRun = false, int $perPage = 50): array
    {
        $oldIp = trim($oldIp);
        $newIp = trim($newIp);
        $types = collect($types)
            ->map(fn($type) => strtoupper(trim((string) $type)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($oldIp === '' || $newIp === '') {
            throw new \InvalidArgumentException('Old and new IP addresses are required.');
        }

        if ($types === []) {
            throw new \InvalidArgumentException('At least one DNS record type is required.');
        }

        $summary = [
            'matched' => 0,
            'updated' => 0,
            'failed' => 0,
            'records' => [],
        ];

        $page = 1;

        while (true) {
            $zones = $this->zones->listZones(page: $page, perPage: $perPage);

            if (empty($zones->result)) {
                break;
            }

            foreach ($zones->result as $zone) {
                $zoneId = $zone->id ?? null;
                $zoneName = $zone->name ?? '(unknown)';

                if (!$zoneId) {
                    continue;
                }

                foreach ($types as $type) {
                    $dnsPage = 1;

                    while (true) {
                        $records = $this->dns->listRecords($zoneId, $type, '', $oldIp, $dnsPage, $perPage);

                        if (empty($records->result)) {
                            break;
                        }

                        foreach ($records->result as $record) {
                            $recordId = $record->id ?? null;
                            $recordContent = trim((string) ($record->content ?? ''));

                            if (!$recordId || $recordContent !== $oldIp) {
                                continue;
                            }

                            $recordName = $record->name ?? '';
                            $recordType = $record->type ?? $type;

                            $summary['matched']++;

                            $summary['records'][] = [
                                'zone_id' => $zoneId,
                                'zone_name' => $zoneName,
                                'record_id' => $recordId,
                                'record_name' => $recordName,
                                'record_type' => $recordType,
                                'old_ip' => $oldIp,
                                'new_ip' => $newIp,
                                'updated' => false,
                            ];
                            $recordIndex = array_key_last($summary['records']);

                            if ($dryRun) {
                                continue;
                            }

                            $ttl = $record->ttl ?? 1;
                            $proxied = property_exists($record, 'proxied') ? (bool) $record->proxied : null;

                            $updated = $this->dns->updateRecordDetails(
                                $zoneId,
                                $recordId,
                                [
                                    'type' => $recordType,
                                    'name' => $recordName,
                                    'content' => $newIp,
                                    'ttl' => $ttl,
                                    ...($proxied !== null ? ['proxied' => $proxied] : []),
                                ]
                            );

                            if ($updated) {
                                $summary['updated']++;
                                $summary['records'][$recordIndex]['updated'] = true;
                            } else {
                                $summary['failed']++;
                            }
                        }

                        $dnsPage++;
                    }
                }
            }

            $page++;
        }

        return $summary;
    }
}
