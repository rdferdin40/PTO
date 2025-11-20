<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Simple Router
 *
 * Registers and dispatches routes to controllers
 */
class Router
{
    private App $app;
    private array $routes = [];

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Register GET route
     */
    public function get(string $path, string $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Register POST route
     */
    public function post(string $path, string $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Add route to registry
     */
    private function addRoute(string $method, string $path, string $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
        ];
    }

    /**
     * Dispatch current request
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove base path if app is in subdirectory
        $basePath = parse_url($this->app->config('app.url'), PHP_URL_PATH) ?? '/';
        if ($basePath !== '/' && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = '/' . trim($uri, '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchRoute($route['path'], $uri);
            if ($params !== false) {
                $this->callHandler($route['handler'], $params);
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo '404 - Page Not Found';
    }

    /**
     * Match route pattern against URI
     */
    private function matchRoute(string $pattern, string $uri): array|false
    {
        // Convert {param} to named capture groups
        $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            // Extract named parameters
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return $params;
        }

        return false;
    }

    /**
     * Call controller handler
     */
    private function callHandler(string $handler, array $params): void
    {
        [$controllerName, $method] = explode('@', $handler);
        $controllerClass = "App\\Controllers\\{$controllerName}";

        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller {$controllerClass} not found");
        }

        $controller = new $controllerClass($this->app);

        if (!method_exists($controller, $method)) {
            throw new \Exception("Method {$method} not found in {$controllerClass}");
        }

        // Call with parameters
        call_user_func_array([$controller, $method], $params);
    }
}
