<?php

namespace App\Providers;

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\LabelController;
use App\Models\Activity;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Label;
use App\Repositories\ActivityRepository;
use App\Repositories\ExpenseRepository;
use App\Repositories\IncomeRepository;
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
            ->give(function (): ExpenseRepository {
                return new ExpenseRepository(Expense::class);
            });

        $this->app
            ->when(IncomeController::class)
            ->needs(RepositoryInterface::class)
            ->give(function (): IncomeRepository {
                return new IncomeRepository(Income::class);
            });

        $this->app
            ->when(ActivityController::class)
            ->needs(RepositoryInterface::class)
            ->give(function (): ActivityRepository {
                return new ActivityRepository(Activity::class);
            });

        $this->app
            ->when(LabelController::class)
            ->needs(RepositoryInterface::class)
            ->give(function (): LabelRepository {
                return new LabelRepository(Label::class);
            });
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
