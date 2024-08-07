<?php

namespace OurEdu\OureduMonitoringPackage;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use OurEdu\OureduMonitoringPackage\Commands\OureduMonitoringPackageCommand;

class OureduMonitoringPackageServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name("ouredu-monitoring-package")->hasConfigFile();
    }
}
