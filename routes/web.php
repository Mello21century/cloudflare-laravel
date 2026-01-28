<?php

use Illuminate\Support\Facades\Route;
use Space\Cloudflare\Http\Controllers\CloudflareController;

Route::group(['middleware' => ['web']], function () {
    Route::get(config('cloudflare.path') . '/attach/{zoneId}', [CloudflareController::class, 'attach'])->name('cloudflare.attach');
    Route::post(config('cloudflare.path') . '/add-cpanel', [CloudflareController::class, 'addCpanel'])->name('cloudflare.add-cpanel');
    Route::post(config('cloudflare.path') . '/update-dns-record/{zoneId}/{recordId}', [CloudflareController::class, 'updateDnsRecord'])->name('cloudflare.update-dns-record');
    Route::get(config('cloudflare.path') . '/delete-dns-record/{zoneId}/{recordId}', [CloudflareController::class, 'deleteDnsRecord'])->name('cloudflare.delete-dns-record');
    Route::post(config('cloudflare.path') . '/add-dns-record/{zoneId}', [CloudflareController::class, 'addDnsRecord'])->name('cloudflare.add-dns-record');
    Route::resource(config('cloudflare.path'), CloudflareController::class)->names('cloudflare');
});
