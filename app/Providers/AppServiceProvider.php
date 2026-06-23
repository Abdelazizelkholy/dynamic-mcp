<?php

namespace App\Providers;

use App\Repositories\Eloquent\IntegrationAuthStepRepository;
use App\Repositories\Eloquent\IntegrationGlobalBodyRepository;
use App\Repositories\Eloquent\IntegrationHeaderRepository;
use App\Repositories\Eloquent\IntegrationRepository;
use App\Repositories\Eloquent\IntegrationServiceHeaderRepository;
use App\Repositories\Eloquent\IntegrationServiceInputGroupRepository;
use App\Repositories\Eloquent\IntegrationServiceInputRepository;
use App\Repositories\Eloquent\IntegrationServiceParamRepository;
use App\Repositories\Eloquent\IntegrationServiceRepository;
use App\Repositories\Eloquent\IntegrationServiceResponseRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\IntegrationAuthStepRepositoryInterface;
use App\Repositories\IntegrationGlobalBodyRepositoryInterface;
use App\Repositories\IntegrationHeaderRepositoryInterface;
use App\Repositories\IntegrationRepositoryInterface;
use App\Repositories\IntegrationServiceHeaderRepositoryInterface;
use App\Repositories\IntegrationServiceInputGroupRepositoryInterface;
use App\Repositories\IntegrationServiceInputRepositoryInterface;
use App\Repositories\IntegrationServiceParamRepositoryInterface;
use App\Repositories\IntegrationServiceRepositoryInterface;
use App\Repositories\IntegrationServiceResponseRepositoryInterface;
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
        $this->app->bind(IntegrationServiceRepositoryInterface::class, IntegrationServiceRepository::class);
        $this->app->bind(IntegrationServiceParamRepositoryInterface::class, IntegrationServiceParamRepository::class);
        $this->app->bind(IntegrationServiceHeaderRepositoryInterface::class, IntegrationServiceHeaderRepository::class);
        $this->app->bind(IntegrationServiceInputGroupRepositoryInterface::class, IntegrationServiceInputGroupRepository::class);
        $this->app->bind(IntegrationServiceInputRepositoryInterface::class,     IntegrationServiceInputRepository::class);
        $this->app->bind(IntegrationGlobalBodyRepositoryInterface::class,        IntegrationGlobalBodyRepository::class);
        $this->app->bind(IntegrationServiceResponseRepositoryInterface::class, IntegrationServiceResponseRepository::class);


    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
