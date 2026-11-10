<?php
namespace App\Core;

class Router
{
    public function dispatch(array $query): void
    {
        $controllerName = $query['controller'] ?? 'produto';
        $actionName     = $query['action'] ?? 'list';

        $controllerClass = 'App\\Controllers\\' . ucfirst($controllerName) . 'Controller';
        $method          = $actionName . 'Action';

        if (!class_exists($controllerClass) || !method_exists($controllerClass, $method)) {
            http_response_code(404);
            echo "Página não encontrada.";
            return;
        }

        $controller = new $controllerClass();
        $controller->$method($query);
    }
}