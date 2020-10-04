<?php

namespace App\Providers;

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LabelController;
use App\Repositories\ActivityRepository;
use App\Repositories\ExpenseRepository;
use App\Repositories\LabelRepository;
use App\Repositories\RepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app
            ->when(ExpenseController::class)
            ->needs(RepositoryInterface::class)
            ->give(ExpenseRepository::class);

        $this->app
            ->when(ActivityController::class)
            ->needs(RepositoryInterface::class)
            ->give(ActivityRepository::class);

        $this->app
            ->when(LabelController::class)
            ->needs(RepositoryInterface::class)
            ->give(LabelRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
