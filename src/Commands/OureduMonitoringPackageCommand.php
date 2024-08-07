<?php

namespace OurEdu\OureduMonitoringPackage\Commands;

use Illuminate\Console\Command;

class OureduMonitoringPackageCommand extends Command
{
    public $signature = 'ouredu-monitoring-package';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
