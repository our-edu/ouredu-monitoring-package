<?php
namespace OurEdu\OureduMonitoringPackage\Listeners;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Redis;
use OurEdu\OureduMonitoringPackage\Services\PrometheusService;

class JobProcessedListener
{
    protected $prometheusService;

    public function __construct(PrometheusService $prometheusService)
    {
        $this->prometheusService = $prometheusService;
    }

    public function handle(JobProcessed $event)
    {
        $jobBody = json_decode($event->job->getRawBody(), true);
        $jobStarted = Redis::Get("job:started:" . $jobBody["uuid"]);
        $jobDelay = (microtime(true) - $jobStarted) * 1000;
        $jobName = $jobBody["displayName"];
        $this->prometheusService->registerJobSuccess($jobName);
        $this->prometheusService->registerJobLatency($jobName, $jobDelay);
        Redis::del("job:started:" . $jobBody["uuid"]);
    }
}
