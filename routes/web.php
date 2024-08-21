<?php

use Illuminate\Support\Facades\Route;
use Space\Cloudflare\Http\Controllers\CloudflareController;

Route::group(['middleware' => ['web']], function () {
    Route::get(config('cloudflare.path') . '/attach/{zoneId}', [CloudflareController::class, 'attach'])->name('cloudflare.attach');
    Route::resource(config('cloudflare.path'), CloudflareController::class)->names('cloudflare');
});
