<?php

namespace Lcg\Console\Stacks;

use Illuminate\Filesystem\Filesystem;
use Lcg\Utils\PathUtil;

trait InstallsInertiaViteStacks
{
    /**
     * Install the Inertia Vue Vite stack.
     *
     * @return void
     */
    protected function installInertiaViteVueStack()
    {
        // Install Inertia...
        $this->requireComposerPackages('inertiajs/inertia-laravel:^0.5.4', 'laravel/sanctum:^2.8', 'tightenco/ziggy:^1.0');

        // NPM Packages...
        $this->updateNodePackages(function ($packages) {
            return [
                // Breeze
                '@inertiajs/inertia' => '^0.11.0',
                '@inertiajs/inertia-vue3' => '^0.6.0',
                '@inertiajs/progress' => '^0.2.7',
                '@tailwindcss/forms' => '^0.5.2',
                '@vitejs/plugin-vue' => '^3.0.0',
                'autoprefixer' => '^10.4.2',
                'postcss' => '^8.4.6',
                'tailwindcss' => '^3.1.0',
                'vue' => '^3.2.31',
                // Expand
                "sass" => "^1.54.4",
                "bootstrap" => "^5.1.3",
                "element-plus" => "^2.2.0",
                "admin-lte" => "3.2",
                "admin-lte-vue" => "file:" . PathUtil::getRelativePath(base_path(), dirname(dirname(dirname(__DIR__))) . "/lte"),
            ] + $packages;
        });

        // Auth Mode...
        if ($this->option('auth') == "create") {
            // Controllers...
            (new Filesystem)->ensureDirectoryExists(app_path('Http/Controllers/Auth'));
            (new Filesystem)->copyDirectory(__DIR__ . '/../../../stubs/inertia-common/app/Http/Controllers/Auth', app_path('Http/Controllers/Auth'));

            // Requests...
            (new Filesystem)->ensureDirectoryExists(app_path('Http/Requests/Auth'));
            (new Filesystem)->copyDirectory(__DIR__ . '/../../../stubs/inertia-common/app/Http/Requests/Auth', app_path('Http/Requests/Auth'));

            // Routes...
            copy(__DIR__ . '/../../../stubs/inertia-common/routes/web.php', base_path('routes/web.php'));
            copy(__DIR__ . '/../../../stubs/inertia-common/routes/auth.php', base_path('routes/auth.php'));
        } else {
            // Providers...
            $this->installServiceProviderAfter('RouteServiceProvider', 'AuthRouteServiceProvider');
            copy(__DIR__ . '/../../../stubs/inertia-common/app/Providers/AuthRouteServiceProvider.php', app_path('Providers/AuthRouteServiceProvider.php'));

            // Routes...
            copy(__DIR__.'/../../../stubs/inertia-common/routes/web_ref.php', base_path('routes/web.php'));
        }

        // Middleware...
        $this->installMiddlewareAfter('SubstituteBindings::class', '\App\Http\Middleware\HandleInertiaRequests::class');

        copy(__DIR__ . '/../../../stubs/inertia-common/app/Http/Middleware/HandleInertiaRequests.php', app_path('Http/Middleware/HandleInertiaRequests.php'));

        // Views...
        copy(__DIR__ . '/../../../stubs/inertia-vite/resources/views/app.blade.php', resource_path('views/app.blade.php'));

        // Components + Pages...
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Components'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Layouts'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Pages'));

        (new Filesystem)->copyDirectory(__DIR__ . '/../../../stubs/inertia-common/resources/js/Components', resource_path('js/Components'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../../stubs/inertia-common/resources/js/Layouts', resource_path('js/Layouts'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../../stubs/inertia-common/resources/js/Pages', resource_path('js/Pages'));

        // Welcome Page
        copy(__DIR__ . '/../../../stubs/inertia-vite/resources/js/Pages/Welcome.vue', resource_path('js/Pages/Welcome.vue'));

        // Tests...
        $this->installTests();

        // "Dashboard" Route...
        $this->replaceInFile('/home', '/dashboard', resource_path('js/Pages/Welcome.vue'));
        $this->replaceInFile('Home', 'Dashboard', resource_path('js/Pages/Welcome.vue'));
        $this->replaceInFile('/home', '/dashboard', app_path('Providers/RouteServiceProvider.php'));

        // Tailwind / Vite...
        copy(__DIR__ . '/../../../stubs/inertia-vite/resources/css/app.css', resource_path('css/app.css'));
        copy(__DIR__ . '/../../../stubs/inertia-vite/postcss.config.js', base_path('postcss.config.js'));
        copy(__DIR__ . '/../../../stubs/inertia-vite/tailwind.config.js', base_path('tailwind.config.js'));
        copy(__DIR__ . '/../../../stubs/inertia-vite/jsconfig.json', base_path('jsconfig.json'));
        copy(__DIR__ . '/../../../stubs/inertia-vite/vite.config.js', base_path('vite.config.js'));
        copy(__DIR__ . '/../../../stubs/inertia-vite/resources/js/app.js', resource_path('js/app.js'));

        if ($this->option('ssr')) {
            $this->installInertiaVueSsrStack();
        }

        $this->components->info('Lcg scaffolding installed successfully.');
        $this->components->warn('Please execute the [npm install && npm run dev] commands to build your assets.');
    }
}
