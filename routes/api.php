<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupabaseController;
use App\Http\Controllers\TeamInvitationController;

// Public routes
Route::post('/auth/login', [SupabaseController::class, 'login']);
Route::post('accept-invite', [TeamInvitationController::class, 'acceptInvitation']);

// Dynamically include all route files in the 'api' directory
foreach (glob(__DIR__ . '/api/*.php') as $routeFile) {
    require $routeFile;
}


