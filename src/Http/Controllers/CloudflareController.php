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
        return view('cloudflare::cloudflare.attach',compact('zoneId'));
    }

    public function store(StoreCpanelRequest $request,Cloudflare $cloudflare)
    {
        try {
        $cloudflare->setCpanel($request->validated('zoneId'), $request->validated('ip'));

        } catch (ResponseException) {

        }
        return to_route('cloudflare.index');
    }

    public function destroy($zoneId,Cloudflare $cloudflare)
    {
        set_time_limit(0);
        $cloudflare->cleanDns($zoneId);
        return redirect()->back();
    }
}
