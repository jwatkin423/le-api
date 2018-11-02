<?php
namespace Adrenalads\CommerceApi;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class CommerceApiServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();

        $this->registerCategoryOptions();

        $this->registerTaxonomy();

        $this->registerFeedManager();

    }

    public function boot()
    {
        $this->publishConfig();
    }

    protected function registerFeedManager() {
        $this->app->singleton('feed-manager', function() {
            return new Manager();
        });
    }


    protected function registerCategoryOptions() {
        $this->app->singleton('CategoryOptions', function() {
            return new CategoryOptions();
        });
    }

    protected function registerTaxonomy() {
        $this->app->singleton('Taxonomy', function() {
            return new Taxonomy();
        });
    }

    private function mergeConfig()
    {
        $path = $this->getConfigPath();
        $this->mergeConfigFrom($path, 'commerce');
    }

    private function publishConfig()
    {
        $path = $this->getConfigPath();
        $this->publishes([$path => config_path('commerce.php')], 'config');
    }

    private function getConfigPath()
    {
        return __DIR__ . '/../config/commerce.php';
    }


}