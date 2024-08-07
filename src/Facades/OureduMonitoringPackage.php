<?php

namespace OurEdu\OureduMonitoringPackage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \OurEdu\OureduMonitoringPackage\OureduMonitoringPackage
 */
class OureduMonitoringPackage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \OurEdu\OureduMonitoringPackage\OureduMonitoringPackage::class;
    }
}
