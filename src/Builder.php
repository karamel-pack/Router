<?php

namespace Karamel\Router;

use Karamel\Router\Exceptions\ActionNotFoundException;
use Karamel\Router\Exceptions\ControllerNotFoundException;
use Karamel\Router\Exceptions\ErrorAtActionNameException;
use Karamel\Router\Exceptions\RouteNotFoundException;
use Karamel\Router\Traits\Restful;

class Builder
{

    private $routes;

    use Restful;

    public function boot($server)
    {
        $requested_path = $this->sanitizeRoutePath($server["PATH_INFO"]);
        $requested_method = $server["REQUEST_METHOD"];

        $route = $this->checkPathExists($requested_path, $requested_method);
        $action = explode("@", $route["action"]);
        if (count($action) !== 2)
            throw new ErrorAtActionNameException();
        $ctr = $action[0];

        if (!class_exists($ctr))
            throw new ControllerNotFoundException($ctr . " Not Found !", 500);

        if (!method_exists($ctr, $action[1]))
            throw new ActionNotFoundException($action[1] . "() action not Found !", 500);

        $controller = new $ctr();

        return $controller->{$action[1]}();

    }

    private function checkPathExists($request_path, $request_method)
    {
        $path_sections = explode("/", $request_path);
        foreach ($this->routes as $route) {
            if ($route["method"] !== $request_method)
                continue;

            $route_sections = explode("/", $route);
            $route_match = true;

            foreach ($route_sections as $index => $route_section) {

                if (!isset($path_sections[$index]))
                    $route_match = false;

                if ($route_sections === $path_sections[$index])
                    continue;
            }

            if ($route_match)
                return $route;
        }

        throw new RouteNotFoundException();
    }

    private function sanitizeRoutePath($path)
    {
        if (substr($path, 0, 1) == '/')
            $path = substr($path, 1);

        if (substr($path, strlen($path) - 1, 1) == '/')
            $path = substr($path, 0, strlen($path) - 1);

        return $path;
    }


    private function addRoute($path, $action, $method)
    {
        $this->routes[] = [
            "method" => $method,
            "path" => $path,
            "action" => $action
        ];

        return $this;
    }
}