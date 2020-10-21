<?php
namespace Karamel\Router\Traits;
trait Restful {
    public function get($path, $action)
    {
        $path = $this->sanitizeRoutePath($path);
        return $this->addRoute($path, $action, 'GET');

    }

}