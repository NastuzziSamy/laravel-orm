<?php

namespace NastuzziSamy\Laravel\Traits;

Trait StaticCallable {
    public static function __callStatic(string $method, array $args) {
        return (new static())->__call($method, $args);
    }

    public function __call(string $method, array $args) {
        if (method_exists($this, $method)) {
            return $this->$method(...$args) ?? $this;
        }
    }
}
