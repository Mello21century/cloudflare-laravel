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

    public function addDomain($domain): \stdClass
    {
        return $this->zones->addZone(name: $domain);
    }

    public function getDns(string $zoneId, $perPage = 50): ?array
    {
        return $this->dns->listRecords(zoneID: $zoneId, perPage: $perPage)?->result;
    }

    public function setDns(string $zoneId, string $name, string $content, string $type = 'A', string $priority = ''): bool
    {
        return $this->dns->addRecord($zoneId, $type, $name, $content, proxied: false, priority: $priority);
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
        $this->dns->batchRecords(zoneID: $zoneId,posts: $posts);
    }

    public function cleanDns(string $zoneId): bool
    {
        $records = $this->getDns($zoneId, 200);
        $ids = collect($records)->pluck('id')->map(fn($id) => ['id' => $id]);
        return $this->dns->batchRecords($zoneId, $ids->toArray());
    }
}
