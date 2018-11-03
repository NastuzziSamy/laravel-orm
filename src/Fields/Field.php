<?php

namespace NastuzziSamy\Laravel\Fields;

use Illuminate\Database\Schema\ColumnDefinition;
use NastuzziSamy\Laravel\Traits\StaticCallable;

abstract class Field
{
    use StaticCallable;

    protected $name;
    protected $column;
    protected $type;
    protected $fillable = true;
    protected $locked = false;

    public function __construct()
    {
        $this->column = new ColumnDefinition(array_merge(
            $this->_getDefaultProperties(),
            [
                'type' => $this->type,
            ]
        ));
    }

    public function __call(string $method, array $args) {
        $this->_checkLock();

        if (method_exists($this, $method)) {
            return $this->$method(...$args) ?? $this;
        } else {
            $this->column = $this->column->$method(...$args);

            return $this;
        }
    }

    public function __get($key)
    {
        if (property_exists($this, $key)) {
            return $this->$key;
        }
        else {
            return $this->column->$key;
        }
    }

    public function __set($key, $value)
    {
        $this->_checkLock();

        $this->column->$key = $value;
    }

    public function __isset($key)
    {
        return isset($this->column->$key);
    }

    public function __unset($key)
    {
        $this->_checkLock();

        unset($this->column->$key);
    }

    protected function _name(string $value) {
        $this->_checkLock();

        $this->name = $value;

        $this->column->name = $value;
    }

    public function getName() {
        return $this->name;
    }

    public function getFieldName() {
        return $this->name;
    }

    protected function lock(string $name) {
        $this->_name($name);

        $this->locked = true;
    }

    protected function fillable(bool $fillable = true) {
        $this->_checkLock();

        $this->fillable = $fillable;
    }

    protected function _checkLock() {
        if ($this->locked) {
            throw new \Exception('The field is locked, nothing can change');
        }
    }

    abstract protected function _getDefaultProperties(): array;

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
        return $model->where($this->columnName, ...$args);
    }
}
