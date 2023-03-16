<?php

namespace Lcg\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Lcg\Console\Traits\Installs;
use Symfony\Component\Process\Process;

class SwaggerCommand extends Command{
    use Installs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lcg:swagger
                            {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install swagger module';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        //依赖
        $this->requireComposerPackages('zircote/swagger-php:^4.4');

        //拷贝yaml
        copy(__DIR__ . '/../../stubs/swagger/swagger.php', public_path('swagger.php'));

        //拷贝swagger-ui
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/swagger/swagger-ui', public_path('swagger'));

        //拷贝provider
        $this->installServiceProviderAfter('RouteServiceProvider', 'SwaggerServiceProvider');
        copy(__DIR__ . '/../../stubs/swagger/providers/SwaggerServiceProvider.php', app_path('Providers/SwaggerServiceProvider.php'));

        //输出结果
        $this->components->info('Swagger installed successfully.');
    }

    /**
     * Installs the given Composer Packages into the application.
     *
     * @param  mixed  $packages
     * @return void
     */
    protected function requireComposerPackages($packages)
    {
        $composer = $this->option('composer');

        if ($composer !== 'global') {
            $command = ['php', $composer, 'require'];
        }

        $command = array_merge(
            $command ?? ['composer', 'require'],
            is_array($packages) ? $packages : func_get_args()
        );

        (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }
}
