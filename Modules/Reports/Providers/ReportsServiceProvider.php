<?php

namespace Modules\Reports\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Modules\Reports\Entities\Reports;

// Module alias
define('REPORTS_MODULE', 'reports');

class ReportsServiceProvider extends ServiceProvider
{
    const REPORT_CONVERSATIONS = 'conversations';
    const REPORT_PRODUCTIVITY  = 'productivity';
    const REPORT_SATISFACTION  = 'satisfaction';
    const REPORT_TIME          = 'time';

    const MAX_TABLE_ITEMS = 20;

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->hooks();
    }

    /**
     * Module hooks.
     */
    public function hooks()
    {
        // Add module's CSS file to the application layout.
        \Eventy::addFilter('stylesheets', function($styles) {
            $styles[] = \Module::getPublicPath(REPORTS_MODULE).'/css/module.css';
            return $styles;
        });

        // Add module's JS file to the application layout.
        \Eventy::addFilter('javascripts', function($javascripts) {
            $javascripts[] = \Module::getPublicPath(REPORTS_MODULE).'/js/laroute.js';
            //$javascripts[] = \Module::getPublicPath(REPORTS_MODULE).'/js/highcharts.js';
            $javascripts[] = \Module::getPublicPath(REPORTS_MODULE).'/js/module.js';
            return $javascripts;
        });

        // Add item to the mailbox menu
        \Eventy::addAction('menu.append', function($mailbox) {
            if (Reports::canViewReports()) {
                echo \View::make('reports::partials/menu', [])->render();
            }
        });

        \Eventy::addFilter('menu.selected', function($menu) {
            if (Reports::canViewReports()) {
                $menu['reports'] = [
                    'reports.conversations',
                    'reports.productivity',
                    'reports.satisfaction',
                    'reports.time',
                ];
            }
            return $menu;
        });
    }

    public static function getCustomFieldFilters()
    {
        $custom_fields = [];

        if (\Module::isActive('customfields')) {

            $mailbox_ids = auth()->user()->mailboxesIdsCanView();

            if ($mailbox_ids) {
                $custom_fields = \Modules\CustomFields\Entities\CustomField::whereIn('mailbox_id', $mailbox_ids)
                    ->get();
            }
        }
        return $custom_fields;
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerTranslations();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('reports.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'reports'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/reports');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/reports';
        }, \Config::get('view.paths')), [$sourcePath]), 'reports');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $this->loadJsonTranslationsFrom(__DIR__ .'/../Resources/lang');
    }

    /**
     * Register an additional directory of factories.
     * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
