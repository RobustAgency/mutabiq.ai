<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupabaseController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\User\MemberController;
use App\Http\Controllers\User\OrganizationController as UserOrganizationController;

// Public routes
Route::post('/auth/login', [SupabaseController::class, 'login']);
Route::post('accept-invite', [TeamInvitationController::class, 'acceptInvitation']);

require __DIR__ . '/api/admin.php';
require __DIR__ . '/api/user.php';
