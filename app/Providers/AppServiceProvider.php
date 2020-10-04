<?php

namespace App\Providers;

use App\Http\Controllers\ExpenseController;
use App\Repositories\ExpenseRepository;
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
