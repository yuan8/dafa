<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Gate::define('is_provos', function ($user) {
            return $user->role<=3;
        });

        Gate::define('provos_and_gate', function ($user) {
            return in_array($user->role, [2,3]);
        });

        Gate::define('is_gate', function ($user) {
            return $user->role<=2;
        });

        Gate::define('gate_check_out_provos', function ($user) {
            $now=Carbon::now()->format('Y-m-d').' 16:00';
            $now=Carbon::parse($now);
            if($user->role==3 ){
                return Carbon::now()->gte($now);
            }else if($user->role<=3){
                return true;
            }
        });

        Gate::define('is_admin', function ($user) {
            return $user->role<=1;
        });

        Gate::define('supper_admin', function ($user) {
            return $user->role==0;
        });

        //
    }
}
