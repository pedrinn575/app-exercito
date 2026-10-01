<?php
/**
 * Roteador HTTP simples.
 * Responsabilidade: mapear método + URI para Controller@ação.
 */

namespace App\Config;

use App\Exceptions\NotFoundException;

final class Router
{
    /** @var array<string, array<string, array{0:class-string,1:string}>> */
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        // Remove /public ou index.php se existir no path
        $path = preg_replace('#^/public#', '', $path) ?? $path;
        $path = $this->normalize($path);

        $handler = $this->routes[$method][$path] ?? null;
        $params = [];

        // Rotas dinâmicas: /militares/{id}/editar
        if ($handler === null) {
            $matched = $this->matchDynamic($method, $path);
            if ($matched !== null) {
                [$handler, $params] = $matched;
            }
        }

        if ($handler === null) {
            throw new NotFoundException('Rota não encontrada: ' . $path);
        }

        [$class, $action] = $handler;
        $controller = new $class();
        $controller->$action(...$params);
    }

    /**
     * @return array{0: array{0:class-string,1:string}, 1: list<string>}|null
     */
    private function matchDynamic(string $method, string $path): ?array
    {
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            if (!str_contains($route, '{')) {
                continue;
            }
            $pattern = preg_replace('#\{([a-zA-Z_]+)\}#', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches);
                return [$handler, array_values($matches)];
            }
        }
        return null;
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
