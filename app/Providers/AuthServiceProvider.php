<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use Carbon\CarbonInterval;
use Illuminate\Support\Carbon;
use Laravel\Passport\Passport;
use App\Enum\Passports\ScopeEnum;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // newer version require to enablePasswordGrant to able to use it
        Passport::enablePasswordGrant();
        
        // Passport::useClientModel(Client::class);

        // need to declare tokensCan, else generate token with scope will error
        Passport::tokensCan(ScopeEnum::can());

        Passport::tokensExpireIn(CarbonInterval::days(15));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));
        Passport::personalAccessTokensExpireIn(CarbonInterval::months(6));
    }
}
