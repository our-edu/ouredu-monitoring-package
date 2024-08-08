<?php

namespace OurEdu\OureduMonitoringPackage\Listeners;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Redis;
use OurEdu\OureduMonitoringPackage\Services\PrometheusService;

class JobProcessingListener
{
    protected $prometheusService;

    public function __construct(PrometheusService $prometheusService)
    {
        $this->prometheusService = $prometheusService;
    }

    public function handle(JobProcessing $event)
    {
        $jobBody = json_decode($event->job->getRawBody(), true);
        Redis::Set("job:started:" . $jobBody["uuid"], microtime(true));
        $this->prometheusService->registerJobStart($jobBody["displayName"]);
    }
}
