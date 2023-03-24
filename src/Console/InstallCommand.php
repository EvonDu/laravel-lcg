<?php

namespace Lcg\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Lcg\Console\Traits\Installs;
use Lcg\Utils\PathUtil;

class InstallCommand extends Command
{
    use Installs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lcg:install {stack=auto : The development stack that should be installed (vite,webpack)}
                            {--inertia : Indicate that the Vue Inertia stack should be installed (Deprecated)}
                            {--pest : Indicate that Pest should be installed}
                            {--ssr : Indicates if Inertia SSR support should be installed}
                            {--auth=ref : Install the mode indicating the authentication (ref,create)}
                            {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the laravel code generators controllers and resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        if($this->argument('stack') === 'vite'){
            $this->installInertiaViteVueStack();
        }
        else if($this->argument('stack') === 'webpack'){
            $this->installInertiaWebpackVueStack();
        }
        else{
            if(app()->version()[0] >= 9){
                $this->installInertiaViteVueStack();
            } else {
                $this->installInertiaWebpackVueStack();
            }
        }
    }

    /**
     * Install laravel code generators tests.
     *
     * @return void
     */
    protected function installTests()
    {
        (new Filesystem)->ensureDirectoryExists(base_path('tests/Feature/Auth'));

        $stubStack = $this->argument('stack') === 'api' ? 'api' : 'default';

        if ($this->option('pest')) {
            $this->requireComposerPackages('pestphp/pest:^1.16', 'pestphp/pest-plugin-laravel:^1.1');

            (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/'.$stubStack.'/pest-tests/Feature', base_path('tests/Feature/Auth'));
            (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/'.$stubStack.'/pest-tests/Unit', base_path('tests/Unit'));
            (new Filesystem)->copy(__DIR__.'/../../stubs/'.$stubStack.'/pest-tests/Pest.php', base_path('tests/Pest.php'));
        } else {
            (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/'.$stubStack.'/tests/Feature', base_path('tests/Feature/Auth'));
        }
    }

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
                    '@inertiajs/inertia' => '^0.11.1',
                    '@inertiajs/inertia-vue3' => '^0.6.0',
                    '@inertiajs/progress' => '^0.2.7',
                    '@tailwindcss/forms' => '^0.5.3',
                    '@vitejs/plugin-vue' => '^4.1.0',
                    'autoprefixer' => '^10.4.14',
                    'postcss' => '^8.4.21',
                    'tailwindcss' => '^3.2.7',
                    'vue' => '^3.2.47',
                    // Expand
                    "sass" => "^1.54.4",
                    "bootstrap" => "^5.1.3",
                    "element-plus" => "^2.2.0",
                    "admin-lte" => "3.2",
                    "admin-lte-vue" => "file:" . PathUtil::getRelativePath(base_path(), dirname(dirname(__DIR__)) . "/lte"),
                ] + $packages;
        });

        // Auth Mode...
        if ($this->option('auth') == "create") {
            // Controllers...
            (new Filesystem)->ensureDirectoryExists(app_path('Http/Controllers/Auth'));
            (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/inertia-common/app/Http/Controllers/Auth', app_path('Http/Controllers/Auth'));

            // Requests...
            (new Filesystem)->ensureDirectoryExists(app_path('Http/Requests/Auth'));
            (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/inertia-common/app/Http/Requests/Auth', app_path('Http/Requests/Auth'));

            // Routes...
            copy(__DIR__ . '/../../stubs/inertia-common/routes/web.php', base_path('routes/web.php'));
            copy(__DIR__ . '/../../stubs/inertia-common/routes/auth.php', base_path('routes/auth.php'));
        } else {
            // Providers...
            $this->installServiceProviderAfter('RouteServiceProvider', 'AuthRouteServiceProvider');
            copy(__DIR__ . '/../../stubs/inertia-common/app/Providers/AuthRouteServiceProvider.php', app_path('Providers/AuthRouteServiceProvider.php'));

            // Routes...
            copy(__DIR__.'/../../stubs/inertia-common/routes/web_lcg.php', base_path('routes/web.php'));
        }

        // Navigations...
        copy(__DIR__ . '/../../stubs/inertia-common/routes/navigations.php', base_path('routes/navigations.php'));

        // Middleware...
        $this->installMiddlewareAfter('SubstituteBindings::class', '\App\Http\Middleware\HandleInertiaRequests::class');
        copy(__DIR__ . '/../../stubs/inertia-common/app/Http/Middleware/HandleInertiaRequests.php', app_path('Http/Middleware/HandleInertiaRequests.php'));

        // Providers...
        $this->installServiceProviderAfter('RouteServiceProvider', 'InertiaServiceProvider');
        copy(__DIR__ . '/../../stubs/inertia-common/app/Providers/InertiaServiceProvider.php', app_path('Providers/InertiaServiceProvider.php'));

        // Views...
        copy(__DIR__ . '/../../stubs/inertia-vite/resources/views/app.blade.php', resource_path('views/app.blade.php'));

        // Components + Pages...
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Components'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Layouts'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Pages'));

        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/inertia-common/resources/js/Components', resource_path('js/Components'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/inertia-common/resources/js/Layouts', resource_path('js/Layouts'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/inertia-common/resources/js/Pages', resource_path('js/Pages'));

        // Welcome Page
        copy(__DIR__ . '/../../stubs/inertia-vite/resources/js/Pages/Welcome.vue', resource_path('js/Pages/Welcome.vue'));

        // Tests...
        $this->installTests();

        // "Dashboard" Route...
        $this->replaceInFile('/home', '/dashboard', resource_path('js/Pages/Welcome.vue'));
        $this->replaceInFile('Home', 'Dashboard', resource_path('js/Pages/Welcome.vue'));
        $this->replaceInFile('/home', '/dashboard', app_path('Providers/RouteServiceProvider.php'));

        // Tailwind / Vite...
        copy(__DIR__ . '/../../stubs/inertia-vite/resources/css/app.css', resource_path('css/app.css'));
        copy(__DIR__ . '/../../stubs/inertia-vite/postcss.config.js', base_path('postcss.config.js'));
        copy(__DIR__ . '/../../stubs/inertia-vite/tailwind.config.js', base_path('tailwind.config.js'));
        copy(__DIR__ . '/../../stubs/inertia-vite/jsconfig.json', base_path('jsconfig.json'));
        copy(__DIR__ . '/../../stubs/inertia-vite/vite.config.js', base_path('vite.config.js'));
        copy(__DIR__ . '/../../stubs/inertia-vite/resources/js/app.js', resource_path('js/app.js'));

        if ($this->option('ssr')) {
            $this->installInertiaVueSsrStack();
        }

        $this->components->info('Lcg scaffolding installed successfully.');
        $this->components->warn('Please execute the [npm install && npm run dev] commands to build your assets.');
    }

    /**
     * Install the Inertia Vue Webpack stack.
     *
     * @return void
     */
    protected function installInertiaWebpackVueStack()
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
                    'vue' => '^3.2.36',
                    "vue-loader" => "^16.8.3",
                    // Expand
                    "sass" => "^1.54.4",
                    "sass-loader" => "^12.1.0",
                    "bootstrap" => "^5.1.3",
                    "element-plus" => "^2.2.0",
                    "admin-lte" => "3.2",
                    "admin-lte-vue" => "file:" . PathUtil::getRelativePath(base_path(), dirname(dirname(dirname(__DIR__)))."/lte"),
                ] + $packages;
        });

        // Auth Mode...
        if ($this->option('auth') == "create") {
            // Controllers...
            (new Filesystem)->ensureDirectoryExists(app_path('Http/Controllers/Auth'));
            (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/inertia-common/app/Http/Controllers/Auth', app_path('Http/Controllers/Auth'));

            // Requests...
            (new Filesystem)->ensureDirectoryExists(app_path('Http/Requests/Auth'));
            (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/inertia-common/app/Http/Requests/Auth', app_path('Http/Requests/Auth'));

            // Routes...
            copy(__DIR__.'/../../stubs/inertia-common/routes/web.php', base_path('routes/web.php'));
            copy(__DIR__.'/../../stubs/inertia-common/routes/auth.php', base_path('routes/auth.php'));
        } else {
            // Providers...
            $this->installServiceProviderAfter('RouteServiceProvider', 'AuthRouteServiceProvider');
            copy(__DIR__ . '/../../stubs/inertia-common/app/Providers/AuthRouteServiceProvider.php', app_path('Providers/AuthRouteServiceProvider.php'));

            // Routes...
            copy(__DIR__.'/../../stubs/inertia-common/routes/web_lcg.php', base_path('routes/web.php'));
        }

        // Navigations...
        copy(__DIR__ . '/../../stubs/inertia-common/routes/navigations.php', base_path('routes/navigations.php'));

        // Middleware...
        $this->installMiddlewareAfter('SubstituteBindings::class', '\App\Http\Middleware\HandleInertiaRequests::class');
        copy(__DIR__.'/../../stubs/inertia-common/app/Http/Middleware/HandleInertiaRequests.php', app_path('Http/Middleware/HandleInertiaRequests.php'));

        // Providers...
        $this->installServiceProviderAfter('RouteServiceProvider', 'InertiaServiceProvider');
        copy(__DIR__ . '/../../stubs/inertia-common/app/Providers/InertiaServiceProvider.php', app_path('Providers/InertiaServiceProvider.php'));

        // Views...
        copy(__DIR__.'/../../stubs/inertia-webpack/resources/views/app.blade.php', resource_path('views/app.blade.php'));

        // Components + Pages...
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Components'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Layouts'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Pages'));

        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/inertia-common/resources/js/Components', resource_path('js/Components'));
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/inertia-common/resources/js/Layouts', resource_path('js/Layouts'));
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/inertia-common/resources/js/Pages', resource_path('js/Pages'));

        // Tests...
        $this->installTests();

        // "Dashboard" Route...
        $this->replaceInFile('/home', '/dashboard', resource_path('js/Pages/Welcome.vue'));
        $this->replaceInFile('Home', 'Dashboard', resource_path('js/Pages/Welcome.vue'));
        $this->replaceInFile('/home', '/dashboard', app_path('Providers/RouteServiceProvider.php'));

        // WebPack
        copy(__DIR__.'/../../stubs/inertia-webpack/webpack.mix.js', base_path('webpack.mix.js'));
        copy(__DIR__.'/../../stubs/inertia-webpack/resources/js/app.js', base_path('resources/js/app.js'));

        $this->info('Lcg scaffolding installed successfully.');
        $this->warn('Please execute the [npm install && npm run dev] commands to build your assets.');
    }
}
