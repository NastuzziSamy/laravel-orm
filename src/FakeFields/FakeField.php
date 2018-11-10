<?php

namespace LaravelORM\FakeFields;

use LaravelORM\Traits\StaticCallable;
use LaravelORM\Interfaces\IsAField;

abstract class FakeField implements IsAField
{
    use StaticCallable;

    protected $name;

    protected $locked = false;

    protected function __construct() {

    }

    protected function _name($value) {
        $this->_checkLock();

        $this->name = $value;

        return $this;
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

    protected function _checkLock() {
        if ($this->locked) {
            throw new \Exception('The field is locked, nothing can change');
        }
    }

    public function call($model, ...$args) {
        if (count($args) === 0) {
            return $this->relatedToModel($model);
        }
        else {
            return $this->scopeWhere($model, ...$args);
        }
    }

    abstract public function get($model);
    abstract public function relateToModel($model);
    abstract public function scopeWhere($model, ...$args);
}
