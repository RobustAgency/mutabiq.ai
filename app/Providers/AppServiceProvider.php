<?php

namespace App\Providers;

use App\Models\User;
use App\Enums\UserRole;
use App\Clients\SupabaseClient;
use App\Services\Auth\SupabaseGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use App\Models\RecordOfProcessingActivity;
use App\Observers\RecordOfProcessingActivityObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SupabaseClient::class, function ($app) {
            return new SupabaseClient;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Supabase guard
        Auth::extend('supabase', function ($app, $name, array $config) {
            return new SupabaseGuard(
                $name,
                Auth::createUserProvider($config['provider']),
                $app['request'],
                $app->make(SupabaseClient::class)
            );
        });

        RecordOfProcessingActivity::observe(RecordOfProcessingActivityObserver::class);

        // Set permission scope based on user's organization
        // This ensures users only see permissions scoped to their organization
        // unless they are a super admin
        Auth::resolved(function ($auth) {
            $user = $auth->user();
            if ($user instanceof User) {
                // Skip permission scoping for super admins
                if ($user->role === UserRole::SUPER_ADMIN) {
                    return;
                }
                setPermissionsTeamId($user->organization_id);
            }
        });
    }
}
