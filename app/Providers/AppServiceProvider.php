<?php

namespace App\Providers;

use App\Repositories\Eloquent\IntegrationAuthStepRepository;
use App\Repositories\Eloquent\IntegrationHeaderRepository;
use App\Repositories\Eloquent\IntegrationRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\IntegrationAuthStepRepositoryInterface;
use App\Repositories\IntegrationHeaderRepositoryInterface;
use App\Repositories\IntegrationRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(IntegrationRepositoryInterface::class, IntegrationRepository::class);
        $this->app->bind(IntegrationAuthStepRepositoryInterface::class, IntegrationAuthStepRepository::class);
        $this->app->bind(IntegrationHeaderRepositoryInterface::class, IntegrationHeaderRepository::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
