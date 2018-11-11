<?php

namespace LaravelORM\FakeFields;

use LaravelORM\Interfaces\IsAField;

abstract class FakeField implements IsAField
{
    protected $name;
    protected $locked = false;

    public function __construct() {}

    public static function new(...$args) {
        return new static(...$args);
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

        $this->name($name);

        $this->locked = true;

        return $this;
    }

    public function checkLock() {
        if ($this->locked) {
            throw new \Exception('The field is locked, nothing can change');
        }

        return $this;
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

    public function getPreMigration() {
        return [];
    }

    public function getMigration() {
        return [];
    }

    public function getPostMigration() {
        return [];
    }
}
