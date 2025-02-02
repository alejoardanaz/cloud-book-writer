<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    // protected $policies = [
    //     'App\Models\Book' => 'App\Policies\BookPolicy',
    //     'App\Models\Section' => 'App\Policies\SectionPolicy',
    // ];

    // /**
    //  * Bootstrap services.
    //  */
    // public function boot(): void
    // {
    //     $this->registerPolicies();
    // }

    // protected function registerPolicies(): void
    // {
    //     foreach ($this->policies as $model => $policy) {
    //         Gate::policy($model, $policy);
    //     }
    // }
}
