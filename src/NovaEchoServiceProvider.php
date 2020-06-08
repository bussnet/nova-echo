<?php

namespace Coreproc\NovaEcho;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class NovaEchoServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('nova-echo', __DIR__ . '/../dist/js/nova-echo.js');

            $connection = Config::get('broadcasting.nova', Config::get('broadcasting.default'));

            $config = Config::get("broadcasting.connections.{$connection}");

            $echoConfig = array_merge([
                'broadcaster' => 'pusher',
                'key' => Arr::get($config, 'key'),
                'cluster' => Arr::get($config, 'options.cluster'),
            ], Config::get('nova-echo.echo', []));

            $userChannel = null;
            if (($userModel = Config::get('nova-echo.user_model')) &&
                ($userId = Auth::id())) {
                $userChannel = sprintf(
                    "%s.%s",
                    str_replace('\\', '.', $userModel),
                    $userId
                );
            }

            Nova::provideToScript([
                'echo' => [
                    'config' => $echoConfig,
                    'userChannel' => $userChannel,
                ],
            ]);
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/nova-echo.php' => config_path('nova-echo.php'),
            ]);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/nova-echo.php', 'nova-echo');
    }
}
