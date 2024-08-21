<?php

namespace Space\Cloudflare\Services;

use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\Zones;

class Cloudflare
{

    protected APIKey $key;
    protected Guzzle $adapter;
    private DNS $dns;
    private Zones $zones;

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

    public function getDomains($perPage = 50): ?array
    {
        return $this->zones->listZones(perPage: $perPage)?->result;
    }

    public function getDns(string $zoneId, $perPage = 50): ?array
    {
        return $this->dns->listRecords(zoneID: $zoneId, perPage: $perPage)?->result;
    }

    public function setDns(string $zoneId, string $name, string $content, string $type = 'A', string $priority = ''): bool
    {
        return $this->dns->addRecord($zoneId, $type, $name, $content, proxied: false, priority: $priority);
    }

    public function setCpanel(string $zoneId, string $ip): void
    {
        $this->setDns($zoneId, '@', $ip);
        $this->setDns($zoneId, 'www', $ip);
        $this->setDns($zoneId, 'mail', $ip);
        $this->setDns($zoneId, 'cpcalendars', $ip);
        $this->setDns($zoneId, 'autodiscover', $ip);
        $this->setDns($zoneId, 'cpcontacts', $ip);
        $this->setDns($zoneId, 'whm', $ip);
        $this->setDns($zoneId, 'webdisk', $ip);
        $this->setDns($zoneId, 'autoconfig', $ip);
        $this->setDns($zoneId, 'cpanel', $ip);
        $this->setDns($zoneId, 'webmail', $ip);
        $this->setDns($zoneId, '@', 'mx1.spamfiltering.io.', 'MX', 10);
        $this->setDns($zoneId, '@', 'mx2.spamfiltering.io.', 'MX', 20);
    }

    public function cleanDns(string $zoneId): bool
    {
        $records = $this->getDns($zoneId);
        if (!$records)
            return false;
        foreach ($records as $record) {
            $this->dns->deleteRecord($zoneId, $record->id);
        }
        return true;
    }
}
