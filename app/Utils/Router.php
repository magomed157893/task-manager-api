<?php

namespace App\Utils;

use App\Controller\TaskController;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Throwable;

use function FastRoute\simpleDispatcher;

class Router
{
    private Dispatcher $dispatcher;
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function handle()
    {
        $this->dispatcher = simpleDispatcher(function (RouteCollector $collector) {
            $collector->addRoute('POST', '/api/tasks[/]', [TaskController::class, 'create']);
            $collector->addRoute('GET', '/api/tasks[/]', [TaskController::class, 'getAll']);
            $collector->addRoute('GET', '/api/tasks/{id:\d+}[/]', [TaskController::class, 'getByID']);
            $collector->addRoute('PUT', '/api/tasks/{id:\d+}[/]', [TaskController::class, 'update']);
            $collector->addRoute('DELETE', '/api/tasks/{id:\d+}[/]', [TaskController::class, 'delete']);
        });

        $routeInfo = $this->dispatcher->dispatch($this->request->getMethod(), $this->request->getPath());
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                Response::send(['error' => 'Route not found'], 404);
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = implode(', ', $routeInfo[1]);
                Response::send(['error' => "Supported HTTP methods $allowedMethods"], 405);
            case Dispatcher::FOUND:
                try {
                    [$handler, $method] = $routeInfo[1];
                    $vars = $routeInfo[2];

                    $controller = [new $handler(), $method];
                    $this->request->setPathParams($vars);
                    call_user_func_array($controller, [$this->request]);
                } catch (Throwable $e) {
                    Response::send(['error' => 'Internal server error: ' . $e->getMessage()], 500);
                }
        }
    }
}
