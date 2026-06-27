<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $apiRoutes = [];

    public function get(string $path, callable|array $handler, ?string $permission = null): void
    {
        $this->add('GET', $path, $handler, $permission);
    }

    public function post(string $path, callable|array $handler, ?string $permission = null): void
    {
        $this->add('POST', $path, $handler, $permission);
    }

    public function put(string $path, callable|array $handler, ?string $permission = null): void
    {
        $this->add('PUT', $path, $handler, $permission);
    }

    public function delete(string $path, callable|array $handler, ?string $permission = null): void
    {
        $this->add('DELETE', $path, $handler, $permission);
    }

    public function api(string $method, string $path, callable|array $handler, ?string $permission = null): void
    {
        $this->apiRoutes[] = compact('method', 'path', 'handler', 'permission');
    }

    private function add(string $method, string $path, callable|array $handler, ?string $permission): void
    {
        $this->routes[] = compact('method', 'path', 'handler', 'permission');
    }

    public function dispatch(string $uri, string $method): void
    {
        if ($method === 'HEAD') {
            $method = 'GET';
        }

        $uri = parse_url($uri, PHP_URL_PATH) ?: '/';
        // Normalize Windows/Apache encoded paths (e.g. %20 in folder names)
        $uri = rawurldecode($uri);
        $base = $this->getBasePath();
        if ($base && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base)) ?: '/';
        } elseif (str_ends_with($base, '/public')) {
            $projectBase = substr($base, 0, -7);
            if ($projectBase && str_starts_with($uri, $projectBase)) {
                $uri = substr($uri, strlen($projectBase)) ?: '/';
            }
        }
        $uri = '/' . trim($uri, '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        if (str_starts_with($uri, '/api')) {
            $this->dispatchApi($uri, $method);
            return;
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            $params = $this->match($route['path'], $uri);
            if ($params !== false) {
                $this->run($route['handler'], $route['permission'], $params);
                return;
            }
        }

        http_response_code(404);
        View::render('errors/404', ['title' => 'Page Not Found']);
    }

    private function dispatchApi(string $uri, string $method): void
    {
        foreach ($this->apiRoutes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            $params = $this->match($route['path'], $uri);
            if ($params !== false) {
                if (in_array($method, ['POST', 'PUT', 'DELETE'], true)) {
                    CSRF::verifyRequest();
                }
                $this->run($route['handler'], $route['permission'], $params, true);
                return;
            }
        }
        json_response(['success' => false, 'message' => 'Endpoint not found'], 404);
    }

    private function match(string $pattern, string $uri): array|false
    {
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        if (!preg_match($regex, $uri, $matches)) {
            return false;
        }
        $params = [];
        foreach ($matches as $key => $val) {
            if (!is_int($key)) {
                $params[$key] = $val;
            }
        }
        return $params;
    }

    private function run(callable|array $handler, ?string $permission, array $params, bool $api = false): void
    {
        if ($permission === 'public') {
            // no auth required
        } elseif ($api) {
            Auth::requireAuth();
            if ($permission) {
                Auth::requirePermission($permission);
            }
        } else {
            Auth::requireAuth();
            if ($permission) {
                Auth::requirePermission($permission);
            }
        }

        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class();
            $controller->$method(...array_values($params));
            return;
        }

        $handler(...array_values($params));
    }

    private function getBasePath(): string
    {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = rtrim(str_replace('\\', '/', dirname($script)), '/');
        $requestPath = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
        $projectFolder = basename(BASE_PATH);
        $projectNeedle = '/' . $projectFolder;

        if ($projectFolder !== '' && str_contains($requestPath, $projectNeedle)) {
            return substr($requestPath, 0, strpos($requestPath, $projectNeedle) + strlen($projectNeedle));
        }

        return $base;
    }
}
