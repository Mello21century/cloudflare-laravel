<?php

namespace Space\Cloudflare\Http\Controllers;

use App\Http\Controllers\Controller;
use Cloudflare\API\Adapter\ResponseException;
use Illuminate\Http\Request;
use Space\Cloudflare\Http\Requests\CreateDnsRequest;
use Space\Cloudflare\Http\Requests\CreateDomainRequest;
use Space\Cloudflare\Http\Requests\StoreCpanelRequest;
use Space\Cloudflare\Services\Cloudflare;

class CloudflareController extends Controller
{
    public function index(Cloudflare $cloudflare)
    {
        $domains = $cloudflare->getDomains();
        return view('cloudflare::cloudflare.index', compact('domains'));
    }


    public function show($zoneId, Cloudflare $cloudflare)
    {
        $records = $cloudflare->getDns($zoneId);
        return view('cloudflare::cloudflare.show', compact('zoneId', 'records'));
    }

    public function updateDnsRecord($zoneId, $recordId, Cloudflare $cloudflare, CreateDnsRequest $request)
    {
        $cloudflare->updateDns(zoneId: $zoneId, recordId: $recordId, name: $request->validated('name'), content: $request->validated('content'), type: $request->validated('type'), proxied: (bool)$request->validated('proxied'));

        return to_route('cloudflare.show', $zoneId);
    }


    public function addDnsRecord($zoneId, Cloudflare $cloudflare, CreateDnsRequest $request)
    {
        $cloudflare->setDns(zoneId: $zoneId, name: $request->validated('name'), content: $request->validated('content'), type: $request->validated('type'), proxied: (bool)$request->validated('proxied'));

        return to_route('cloudflare.show', $zoneId);
    }

    public function deleteDnsRecord($zoneId, $recordId, Cloudflare $cloudflare)
    {
        $cloudflare->deleteDns(zoneId: $zoneId, recordId: $recordId);

        return to_route('cloudflare.show', $zoneId);
    }

    public function attach(string $zoneId)
    {
        return view('cloudflare::cloudflare.attach', compact('zoneId'));
    }

    public function create()
    {
        return view('cloudflare::cloudflare.create');
    }

    public function store(CreateDomainRequest $request, Cloudflare $cloudflare)
    {
        $result = $cloudflare->addDomain($request->validated('domain'));
        return redirect()->route('cloudflare::cloudflare.index')
            ->withMessage('Domain created successfully')
            ->withNameServers($result->name_servers);
    }

    public function addCpanel(StoreCpanelRequest $request, Cloudflare $cloudflare, $zoneId)
    {
        try {
            $cloudflare->setCpanel(
                $request->validated('zoneId'),
                $request->validated('ip'),
                $request->validated('spf'),
                $request->validated('dkim')
            );
        } catch (ResponseException $e) {
            dd($e->getMessage());
        }
        return to_route('cloudflare.index');
    }

    public function destroy($zoneId, Cloudflare $cloudflare)
    {
        set_time_limit(0);
        try {
            $cloudflare->cleanDns($zoneId);
        } catch (ResponseException) {

        }
        return redirect()->back();
    }
}
