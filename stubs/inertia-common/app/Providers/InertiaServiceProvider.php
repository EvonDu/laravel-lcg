<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class InertiaServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        // Configure mix_url
        if(config('app.mix_url') === null)
            config(['app.mix_url' => '.']);

        // Cover Inertia\Response
        $callback = function($class){
            if($class == "Inertia\Inertia"){
                require_once base_path("app/Expand/Lcg/Inertia/Response.php");
                return true;
            }
            return false;
        };
        spl_autoload_register($callback, true, true);
    }
}
