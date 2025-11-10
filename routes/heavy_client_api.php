<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;


// Route::get('test/check-rate-limit', fn () => RateLimiter::limiter('heavy_client_api') ? 'registered' : 'not found');