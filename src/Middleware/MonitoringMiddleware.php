<?php

namespace OurEdu\OureduMonitoringPackage\Middleware;

use Closure;
use Illuminate\Http\Request;
use OurEdu\OureduMonitoringPackage\Services\PrometheusService;

class MonitoringMiddleware
{
    private $promService;

    public function __construct(PrometheusService $service)
    {
        $this->promService = $service;
    }

    /**
     * Get the normalized route path.
     *
     * @return string
     */
    protected function getNormalizedRoutePath(Request $request)
    {
        $route = Route::getRoutes()->match($request);

        if (! $route) {
            return $request->path(); // Fallback to the raw path if no route is matched
        }

        $path = $route->uri();

        foreach ($route->parameters() as $key => $value) {
            $path = str_replace($value, ':'.$key, $path);
        }

        return '/'.$path;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $start = microtime(true);
        $response = $next($request);
        $status = $response->getStatusCode();
        $duration = (microtime(true) - $start) * 1000;
        $path = $this->getNormalizedRoutePath($request);
        $method = $request->method();

        // Normalize the path
        $path = '/'.trim($path, '/');

        $method = $request->method();

        $this->promService->registerRequest($method, $path, $status, $duration);

        return $response;
    }
}
