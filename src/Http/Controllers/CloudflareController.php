<?php

namespace Space\Cloudflare\Http\Controllers;

use App\Http\Controllers\Controller;
use Cloudflare\API\Adapter\ResponseException;
use Illuminate\Http\Request;
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

    public function update(StoreCpanelRequest $request, Cloudflare $cloudflare, $zoneId)
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
