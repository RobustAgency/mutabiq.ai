<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupabaseController;
use App\Http\Controllers\TeamInvitationController;

// Public routes
Route::post('/auth/login', [SupabaseController::class, 'login']);
Route::post('accept-invite', [TeamInvitationController::class, 'acceptInvitation']);

require __DIR__ . '/api/admin.php';
require __DIR__ . '/api/user.php';

