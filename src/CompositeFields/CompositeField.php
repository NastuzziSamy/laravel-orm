<?php

namespace LaravelORM\CompositeFields;

use LaravelORM\Interfaces\IsAField;

abstract class CompositeField implements IsAField
{
    protected $name;
    protected $fields = [];
    protected $uniques = [];

    protected $locked = false;

    public function __construct()
    {
        foreach ($this->fields as $key => $value) {
            if (is_string($value)) {
                $this->fields[$key] = new $value;
            }
        }
    }

    public static function new(...$args) {
        return new static(...$args);
    }

    public function getName() {
        return $this->name;
    }

    protected function setName($name) {
        $this->name = $name;

        return $this;
    }

    public function unique() {
        $this->checkLock();

        $this->unique[] = $this->getFields();

        return $this;
    }

    public function getFields()
    {
        return array_values($this->fields);
    }

    public function getUniques()
    {
        return $this->uniques;
    }

    protected function checkLock() {
        if ($this->locked) {
            throw new \Exception('The composite field is locked, nothing can change');
        }

        return $this;
    }

    public function lock(string $name) {
        $this->checkLock();

        $this->setName($name);

        $this->locked = true;

        return $this;
    }

    public function get($mode, $value) {
        return $value;
    }

    public function set($mode, $value) {
        $model->setAttribute($this->name, $value);

        return $this;
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
}
