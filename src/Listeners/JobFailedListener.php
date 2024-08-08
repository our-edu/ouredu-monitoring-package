<?php

namespace OurEdu\OureduMonitoringPackage\Listeners;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Redis;
use OurEdu\OureduMonitoringPackage\Services\PrometheusService;

class JobFailedListener
{
    protected $prometheusService;

    public function __construct(PrometheusService $prometheusService)
    {
        $this->prometheusService = $prometheusService;
    }

    public function handle(JobFailed $event)
    {
        $jobBody = json_decode($event->job->getRawBody(), true);
        $jobStarted = Redis::Get("job:started:" . $jobBody["uuid"]);
        $jobDelay = (microtime(true) - $jobStarted) * 1000;
        $jobName = $jobBody["displayName"];
        $this->prometheusService->registerJobFailure($jobBody["displayName"]);
        $this->prometheusService->registerJobLatency($jobName, $jobDelay);
        Redis::del("job:started:" . $jobBody["uuid"]);
    }
}
