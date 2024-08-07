<?php

namespace OurEdu\OureduMonitoringPackage\Services;

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

class PrometheusService
{
    public CollectorRegistry $collectorRegistry;

    public function __construct()
    {
        $registry = CollectorRegistry::getDefault();
        $this->collectorRegistry = $registry;
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
                ['method', 'url', 'status']
            )
            ->incBy(1, [
                'method' => $method,
                'url' => $url,
                'status' => $status,
            ]);

        $this->collectorRegistry
            ->getOrRegisterHistogram(
                'app',
                'request_latency_ms',
                'Request duration in seconds',
                ['method', 'url'],
                [5, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000, 10000]
            )
            ->observe($duration, ['method' => $method, 'url' => $url]);
    }

    public function registerJobStart(string $jobName): void
    {
        $this->collectorRegistry
            ->getOrRegisterCounter(
                'app',
                'jobs_started_total',
                'Total number of jobs started',
                ['job']
            )
            ->incBy(1, ['job' => $jobName]);
    }

    public function registerJobSuccess(string $jobName): void
    {
        $this->collectorRegistry
            ->getOrRegisterCounter(
                'app',
                'jobs_succeeded_total',
                'Total number of jobs succeeded',
                ['job']
            )
            ->incBy(1, ['job' => $jobName]);
    }

    public function registerJobFailure(string $jobName): void
    {
        $this->collectorRegistry
            ->getOrRegisterCounter(
                'app',
                'jobs_failed_total',
                'Total number of jobs failed',
                ['job']
            )
            ->incBy(1, ['job' => $jobName]);
    }

    public function registerJobLatency(string $jobName, float $latency): void
    {
        $this->collectorRegistry
            ->getOrRegisterHistogram(
                'app',
                'job_latency_in_ms',
                'Job latency in milliseconds',
                ['job'],
                [5, 10, 25, 50, 100, 250, 500, 1000, 2500, 5000, 10000]
            )
            ->observe($latency, ['job' => $jobName]);
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
