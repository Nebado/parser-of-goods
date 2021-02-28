<?php

namespace App\Components;

/**
 * Class Route
 * Routing component
 * 
 */
class Route {
  
    private static $routes = [];
    private static $pathNotFound = null;
    private static $methodNotAllowed = null;

    public static function add($expression, $function, $method = 'get')
    {
        array_push(self::$routes, [
            'expression' => $expression,
            'function' => $function,
            'method' => $method
        ]);
    }

    public static function get($expression, $controller, $method = 'get')
    {
        array_push(self::$routes, [
            'expression' => $expression,
            'controller' => $controller, 
            'method' => $method
        ]); 
    }

    public static function pathNotFound($function)
    {
        self::$pathNotFound = $function;
    }

    public static function methodNotAllowed($function)
    {
        self::$methodNotAllowed = $function;
    }

    public static function run($basePath = '/')
    {
        $parsedUrl = parse_url($_SERVER['REQUEST_URI']);

        if (isset($parsedUrl['path'])) {
            $path = $parsedUrl['path'];
        } else {
            $path = '/';
        }

        $method = $_SERVER['REQUEST_METHOD'];
        $pathMatchFound = false;
        $routeMatchFound = false;

        foreach (self::$routes as $route) {
            if ($basePath != '' && $basePath != '/') {
                $route['expression'] = '('.$basePath.')'.$route['expression'];
            }

            $route['expression'] = '^'.$route['expression'];
            $route['expression'] = $route['expression'].'$';

            if (preg_match('#'.$route['expression'].'#', $path, $matches)) {
                $pathMathFound = true;

                if (strtolower($method) == strtolower($route['method'])) {
                    array_shift($matches);

                    if ($basePath != '' && $basePath != '/') {
                        array_shift($matches);
                    }

                    if (isset($route['function'])) {
                        call_user_func_array($route['function'], $matches);
                    } 

                    if (isset($route['controller'])) {
                        $segments = explode('@', $route['controller']);
                        
                        $controllerName = array_shift($segments);
                        $methodName = array_shift($segments);
                        
                        $parameters = $segments;
                        
                        $controllerFile = $_SERVER['DOCUMENT_ROOT'].'/src/app/Controllers/'.$controllerName.'.php';
                        if (is_file($controllerFile)) {
                            $controllerName = sprintf("\App\Controllers\%s", $controllerName);
                            $controllerObject = new $controllerName;
                        }
                        
                        call_user_func_array(array($controllerObject, $methodName), $parameters);
                    }

                    $routeMatchFound = true;

                    break;
                }
            }
        }

        if (!$routeMatchFound) {
            if ($pathMatchFound) {
                header("HTTP/1.0 405 Method Not Allowed");
            } else {
                header("HTTP/1.0 404 Not Found");
            }
        }
    }  
}
