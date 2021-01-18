<?php

/**
 * Laravel File Auth Driver
 *
 * @author    Jonson B. <www.jbc.bd@gmail.com>
 * @copyright 2021 Jonson B. (https://who-jonson.github.io)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/who-jonson/laravel-file-auth-driver
 */

namespace WhoJonson\LaravelAuth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use WhoJonson\LaravelAuth\Providers\FileUserProvider;

class ServiceProvider extends BaseServiceProvider
{

    /**
     * Bootstrap the application services.
     */
    public function boot() {
        $configPath = __DIR__ . '/../config/auth-driver.php';

        if (function_exists('config_path')) {
            $publishPath = config_path('auth-driver.php');
        } else {
            $publishPath = base_path('config/auth-driver.php');
        }
        $this->publishes([$configPath => $publishPath], 'config');

        $this->bootAuthProviders();
    }

    /**
     * Register the application services.
     */
    public function register() {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/auth-driver.php', 'auth-driver'
        );
    }

    /**
     * Boot Auth Providers
     */
    private function bootAuthProviders() {
        Auth::provider('file', function ($app, array $config) {
            return new FileUserProvider($app['hash'], $config);
        });
    }
}
