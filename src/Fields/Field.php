<?php

namespace NastuzziSamy\Laravel\Fields;

use Illuminate\Database\Schema\ColumnDefinition;

abstract class Field
{
    protected $name;
    protected $column;
    protected $type;
    protected $fillable = true;
    protected $lock = false;

    public function __construct(string $name = null)
    {
        $this->column = new ColumnDefinition(array_merge(
            $this->getDefaultProperties(),
            [
                'type' => $this->type,
            ]
        ));

        $this->name($name);
    }

    // TODO
    public static function __callStatic(string $method, array $args) {
        return (new self)->$method(...$args);
    }

    public function __call(string $method, array $args) {
        $this->checkLock();

        $this->column = $this->column->$method(...$args);

        return $this;
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
        if ($key == 'name') {
            $this->name($value);

            return $value;
        }

        $this->checkLock();

        $this->column->$key = $value;
    }

    public function __isset($key)
    {
        return isset($this->column->$key);
    }

    public function __unset($key)
    {
        $this->checkLock();

        unset($this->column->$key);
    }

    public function name($value) {
        $this->checkLock();

        $this->name = $value;

        $this->column->name = $value;

        return $this;
    }

    public function checkLock() {
        if ($this->lock) {
            throw new \Exception('The field is locked, nothing can change');
        }
    }

    public function lock(string $name) {
        if (!$this->name) {
            $this->name($name);
        }

        $this->lock = true;

        return $this;
    }

    public function fillable(bool $fillable = true) {
        $this->fillable = $fillable;

        return $this;
    }

    abstract public function getDefaultProperties(): array;

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
            return $this;
        }
        else {
            return $model->where($this->columnName, ...$args);
        }
    }
}
