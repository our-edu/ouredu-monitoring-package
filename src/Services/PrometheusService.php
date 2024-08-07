<?php

namespace OurEdu\OureduMonitoringPackage\Services;

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

class PrometheusService
{
    public CollectorRegistry $collectorRegistry;
    private $appName;

    public function __construct()
    {
    \Prometheus\Storage\Redis::setDefaultOptions(
        [
            'host' => env("REDIS_HOST"),
            'port' => env("REDIS_PORT"),
            'password' => env("REDIS_PASSWORD"),
        ]
    );
    $registry = CollectorRegistry::getDefault();
        $this->collectorRegistry = $registry;
        $this->appName = config("app.name") ?? "DefaultApp";
    }

    public function registerRequest(
        string $method,
        string $url,
        int $status,
        float $duration
    ): void {
        $this->collectorRegistry
            ->getOrRegisterCounter(
                'app',
                'requests_count',
                'Total number of HTTP requests',
                ['method', 'url', 'status', 'app']
            )
            ->incBy(1, [
                'method' => $method,
                'url' => $url,
                'status' => $status,
                'app' => $this->appName
            ]);

        $this->collectorRegistry
            ->getOrRegisterHistogram(
                'app',
                'request_latency_ms',
                'Request duration in seconds',
                ['method', 'url', 'app'],
                [5, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000, 10000]
            )
            ->observe($duration, ['method' => $method, 'url' => $url, 'app' => $this->appName]);

    }

    public function registerJobStart(string $jobName): void
    {
        $this->collectorRegistry
            ->getOrRegisterCounter(
                'app',
                'jobs_started_total',
                'Total number of jobs started',
                ['job', 'app']
            )
            ->incBy(1, ['job' => $jobName, 'app' => $this->appName]);
    }

    public function registerJobSuccess(string $jobName): void
    {
        $this->collectorRegistry
            ->getOrRegisterCounter(
                'app',
                'jobs_succeeded_total',
                'Total number of jobs succeeded',
                ['job','app']
            )
            ->incBy(1, ['job' => $jobName, 'app' => $this->appName]);
    }

    public function registerJobFailure(string $jobName): void
    {
        $this->collectorRegistry
            ->getOrRegisterCounter(
                'app',
                'jobs_failed_total',
                'Total number of jobs failed',
                ['job', 'app']
            )
            ->incBy(1, ['job' => $jobName, 'app' => $this->appName]);
    }

    public function registerJobLatency(string $jobName, float $latency): void
    {
        $this->collectorRegistry
            ->getOrRegisterHistogram(
                'app',
                'job_latency_in_ms',
                'Job latency in milliseconds',
                ['job', 'app'],
                [5, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000, 10000]
            )
            ->observe($latency, ['job' => $jobName, 'app' => $this->appName]);
    }

    public function metrics()
    {
        $renderer = new RenderTextFormat;

        $result = $renderer->render(
            $this->collectorRegistry->getMetricFamilySamples()
        );

        return response($result, 200, [
            'Content-Type' => RenderTextFormat::MIME_TYPE,
        ]);
    }
}