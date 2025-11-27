<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    /**
     * Actions that should be logged.
     */
    protected array $loggedActions = [
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
    ];

    /**
     * Routes that should be excluded from logging.
     */
    protected array $excludedRoutes = [
        'login',
        'logout',
        'api/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated users
        if (!auth()->check()) {
            return $response;
        }

        // Only log certain HTTP methods
        if (!in_array($request->method(), $this->loggedActions)) {
            return $response;
        }

        // Check if route should be excluded
        if ($this->shouldExclude($request)) {
            return $response;
        }

        // Log the activity
        $this->logActivity($request, $response);

        return $response;
    }

    /**
     * Check if the route should be excluded from logging.
     */
    protected function shouldExclude(Request $request): bool
    {
        $routeName = $request->route()?->getName();
        $routePath = $request->path();

        foreach ($this->excludedRoutes as $excluded) {
            if ($routeName === $excluded || fnmatch($excluded, $routePath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Log the user activity.
     */
    protected function logActivity(Request $request, Response $response): void
    {
        $routeName = $request->route()?->getName() ?? $request->path();
        $method = $request->method();

        // Determine action based on route name or method
        $action = $this->determineAction($routeName, $method);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => "{$method} request to {$routeName}",
            'properties' => [
                'route' => $routeName,
                'method' => $method,
                'status_code' => $response->getStatusCode(),
                'parameters' => $request->except(['password', 'password_confirmation', '_token']),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Determine the action name from route.
     */
    protected function determineAction(string $routeName, string $method): string
    {
        // Try to extract action from route name
        $parts = explode('.', $routeName);
        
        if (count($parts) >= 2) {
            $resource = $parts[count($parts) - 2] ?? 'unknown';
            $action = $parts[count($parts) - 1] ?? 'unknown';
            return "{$action}_{$resource}";
        }

        // Fallback to method-based action
        return match ($method) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'action',
        };
    }
}

