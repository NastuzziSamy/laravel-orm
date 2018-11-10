<?php

namespace LaravelORM\Fields;

use Illuminate\Database\Schema\ColumnDefinition;
use LaravelORM\Traits\StaticCallable;
use LaravelORM\Interfaces\IsAField;

abstract class Field implements IsAField
{
    use StaticCallable;

    protected $name;
    protected $type;
    protected $properties = [];
    protected $visible = true;
    protected $fillable = true;
    protected $locked = false;

    public function __construct() {}

    public function __call(string $method, array $args) {
        if (method_exists($this, $method)) {
            return $this->$method(...$args) ?? $this;
        } else {
            $this->_checkLock();

            if (count($args) === 0) {
                $this->properties[$method] = true;
            }
            elseif (count($args) === 1) {
                $this->properties[$method] = $args[0];
            }
            else {
                $this->properties[$method] = $args;
            }

            return $this;
        }
    }

    public function __get($key)
    {
        if (property_exists($this, $key)) {
            return $this->$key;
        }
        else {
            return $this->properties[$key];
        }
    }

    public function __set($key, $value)
    {
        $this->_checkLock();

        $this->properties[$key] = $value;
    }

    public function __isset($key)
    {
        return isset($this->properties[$key]);
    }

    public function __unset($key)
    {
        $this->_checkLock();

        unset($this->properties[$key]);
    }

    public function getProperties() {
        return $this->properties;
    }

    public function hasProperty($key) {
        return $this->__isset($key);
    }

    public function getProperty($key) {
        return $this->__get($key);
    }

    public function setProperty($key, $value) {
        return $this->__set($key, $value);
    }

    public function getName() {
        return $this->name;
    }

    protected function lock(string $name) {
        $this->_checkLock();

        $this->name = $name;

        $this->locked = true;
    }

    protected function fillable(bool $fillable = true) {
        $this->_checkLock();

        $this->fillable = $fillable;
    }

    protected function visible(bool $visible = true) {
        $this->_checkLock();

        $this->visible = $visible;
    }

    protected function _checkLock() {
        if ($this->locked) {
            throw new \Exception('The field is locked, nothing can change');
        }
    }

    public function get($model) {
        $value = $model->getAttribute($this->name);

        if ($value === null) {
            return $value;
        }

        switch ($this->type) {
            case 'int':
            case 'integer':
                return (int) $value;

            case 'bool':
            case 'boolean':
                return (bool) $value ;

            case 'float':
            case 'double':
            case 'real':
                return (float) $value;

            case 'string':
                return (string) $value;

            default:
                return $value;
        }
    }

    public function call($model, ...$args) {
        if (count($args) === 0) {
            return $this->relateToModel($model);
        }
        else {
            return $this->scopeWhere($model, ...$args);
        }
    }

    public function relateToModel($model) {
        return $this;
    }

    public function scopeWhere($model, ...$args) {
        return $model->where($this->name, ...$args);
    }

    public function getPreMigration() {
        return [];
    }

    public function getMigration() {
        return array_merge([
            $this->type => $this->getName(),
        ], $this->getProperties());
    }

    public function getPostMigration() {
        return [];
    }
}
