<?php

namespace App\Providers;

use App\Filters\AbstractFilter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Builder::macro('toFilter',
            fn(AbstractFilter $filter, array $queryStrings) => $filter->filterByDecorators($this, $queryStrings)
        );
    }
}
