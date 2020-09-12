<?php

namespace Sammyjo20\Lasso\Commands;

use Illuminate\Console\Command;
use Sammyjo20\Lasso\Container\Artisan;
use Sammyjo20\Lasso\Container\Console;
use Sammyjo20\Lasso\Helpers\ConfigValidator;
use Sammyjo20\Lasso\Helpers\Filesystem;
use Sammyjo20\Lasso\Tasks\Publish\PublishJob;
use Sammyjo20\Lasso\Services\Bundler;

final class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lasso:publish {--no-git} {--silent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compile assets and push assets to the specified Lasso Filesystem Disk.';

    /**
     * Execute the console command.
     *
     * @param Artisan $artisan
     * @param Filesystem $filesystem
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Sammyjo20\Lasso\Exceptions\CommittingFailed
     * @throws \Sammyjo20\Lasso\Exceptions\ConfigFailedValidation
     */
    public function handle(Artisan $artisan, Filesystem $filesystem)
    {
        $artisan->setCommand($this);

        $use_git = $this->option('no-git') === false;
        $silent_mode = $this->option('silent');

        (new ConfigValidator())->validate();

        $env = config('lasso.storage.environment', null);

        if ($silent_mode === false && is_null($env) === false) {
            $env = $this->ask('🐎 Which Lasso environment would you like to publish to?', $env);

            $filesystem->setLassoEnvironment($env);
        }

        (new PublishJob())
            ->run();

        dd('Done');

        $disk = config('lasso.storage.disk');

        $this->info('🏁 Preparing to publish assets to "' . $disk . '" Filesystem.');

        (new Bundler($env))->execute($use_git);

        $this->info('✅ Successfully published assets to "' . $disk  . '" Filesystem! Yee-haw! 🐎');
    }
}
