<?php

namespace LaravelORM\Fields;

use Illuminate\Database\Schema\ColumnDefinition;
use LaravelORM\Interfaces\IsAField;

abstract class Field implements IsAField
{
    protected $name;
    protected $type;
    protected $properties = [];
    protected $visible = true;
    protected $fillable = true;
    protected $locked = false;

    public function __construct() {}

    public static function new(...$args) {
        return new static(...$args);
    }

    public function __call(string $method, array $args) {
        $this->checkLock();

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
        $this->checkLock();

        $this->properties[$key] = $value;
    }

    public function __isset($key)
    {
        return isset($this->properties[$key]);
    }

    public function __unset($key)
    {
        $this->checkLock();

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

    protected function setName($value) {
        $this->name = $value;

        return $this;
    }

    public function lock(string $name) {
        $this->checkLock();

        $this->setName($name);

        $this->locked = true;

        return $this;
    }

    public function fillable(bool $fillable = true) {
        $this->checkLock();

        $this->fillable = $fillable;

        return $this;
    }

    public function visible(bool $visible = true) {
        $this->checkLock();

        $this->visible = $visible;

        return $this;
    }

    public function hidden(bool $hidden = true) {
        return $this->visible(!$hidden);
    }

    public function checkLock() {
        if ($this->locked) {
            throw new \Exception('The field is locked, nothing can change');
        }

        return $this;
    }

    public function getValue($model, $value) {
        return $value;
    }

    public function setValue($model, $value) {
        return $value;
    }

    public function relationValue($model) {
        return $this->whereValue($model, $model->{$this->$name});
    }

    public function whereValue($query, ...$args) {
        return $query->where($this->name, ...$args);
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
