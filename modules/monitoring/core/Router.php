<?php
class Router {
    private $routes = [];
    private $basePath = '';
    
    public function __construct($basePath = '') {
        $this->basePath = $basePath;
    }
    
    // Add a route
    public function addRoute($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    // Dispatch the request
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        
        // Remove query string and base path
        $uri = strtok($uri, '?');
        $uri = str_replace($this->basePath, '', $uri);
        $uri = trim($uri, '/');
        
        // Collect request data
        $postData = [];
        $uploadedFiles = [];
        
        if ($method === 'POST' || $method === 'PUT') {
            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
            if (strpos($contentType, 'multipart/form-data') !== false) {
                $postData = $_POST;
                $uploadedFiles = $_FILES;
            } elseif (strpos($contentType, 'application/json') !== false) {
                $input = file_get_contents('php://input');
                $postData = json_decode($input, true) ?: [];
            } else {
                $postData = $_POST;
            }
        }
        
        // Find matching route
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            $pattern = $this->convertPathToRegex($route['path']);
            if (preg_match($pattern, $uri, $matches)) {
                // Extract parameters
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                
                // Instantiate controller
                $controllerClass = $route['controller'];
                if (!class_exists($controllerClass)) {
                    $controllerFile = "../controllers/{$controllerClass}.php";
                    if (file_exists($controllerFile)) {
                        require_once $controllerFile;
                    } else {
                        http_response_code(500);
                        return ['error' => 'Controller not found'];
                    }
                }
                $controller = new $controllerClass();
                
                // Call action with parameters
                $action = $route['action'];
                
                // ⭐ FIX: Special handling for login with 2 parameters
                if ($action === 'login' && $controllerClass === 'AuthController') {
                    $username = $postData['username'] ?? '';
                    $password = $postData['password'] ?? '';
                    return $controller->$action($username, $password);
                }
                
                // Special handling for markAttendance with files
                if ($action === 'markAttendance' && !empty($uploadedFiles)) {
                    return $controller->$action($postData, $uploadedFiles);
                }
                
                // Check how many parameters the method expects
                try {
                    $reflection = new ReflectionMethod($controller, $action);
                    $params_needed = $reflection->getNumberOfParameters();
                    
                    if ($params_needed >= 1 && !empty($postData)) {
                        return $controller->$action($postData);
                    } elseif (!empty($params)) {
                        return $controller->$action($params);
                    } else {
                        return $controller->$action();
                    }
                } catch (ReflectionException $e) {
                    // Method doesn't exist or can't be reflected
                    if (!empty($postData)) {
                        return $controller->$action($postData);
                    } elseif (!empty($params)) {
                        return $controller->$action($params);
                    } else {
                        return $controller->$action();
                    }
                }
            }
        }
        
        // No route found
        http_response_code(404);
        return ['error' => 'Route not found'];
    }
    
    // Convert path pattern to regex
    private function convertPathToRegex($path) {
        // Replace {param} with named capture groups
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = str_replace('/', '\/', $pattern);
        return '/^' . $pattern . '$/';
    }
}
?>