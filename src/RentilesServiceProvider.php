<?php

namespace PixellWeb\Rentiles;

use App\Http\Middleware\TrimStrings;
use Illuminate\Support\ServiceProvider;
use PixellWeb\Rentiles\app\Console\Commands\Test;


class RentilesServiceProvider extends ServiceProvider
{

    protected $commands = [
        Test::class,
    ];


    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->addCustomConfigurationValues();
    }

    public function addCustomConfigurationValues()
    {
        // add filesystems.disks for the log viewer
        config([
            'logging.channels.'.config('rentiles.logging_channel') => [
                'driver' => 'single',
                'path' => storage_path('logs/'.config('rentiles.logging_channel').'.log'),
                'level' => 'debug',
            ]
        ]);

    }


    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/rentiles.php', 'rentiles'
        );

        // register the artisan commands
        $this->commands($this->commands);
    }
}
