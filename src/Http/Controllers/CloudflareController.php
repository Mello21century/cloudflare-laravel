<?php

namespace Space\Cloudflare\Http\Controllers;

use App\Http\Controllers\Controller;
use Cloudflare\API\Adapter\ResponseException;
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

    public function store(StoreCpanelRequest $request, Cloudflare $cloudflare)
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
