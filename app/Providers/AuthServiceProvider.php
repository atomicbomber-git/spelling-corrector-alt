<?php

namespace App\Providers;

use App\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    const MANAGE_MAHASISWA = "manage_mahasiswa";
    const MANAGE_DOKUMEN_WORD = "manage_dokumen_word";

    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define(self::MANAGE_MAHASISWA, function (User $user) {
            return in_array($user->level, [
                User::LEVEL_ADMIN,
            ]);
        });

        Gate::define(self::MANAGE_DOKUMEN_WORD, function (User $user) {
            return in_array($user->level, [
                User::LEVEL_MAHASISWA,
            ]);
        });
    }
}
