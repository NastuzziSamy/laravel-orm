<?php

namespace LaravelORM;

class FieldManager{
    protected $schema;

    public function __construct($schema) {
        $this->schema = $schema;
    }

    public function __get($name) {
        return $this->schema->getFieldOrFake($name);
    }

    public function __set($name, $value) {
        $this->schema->setField($name, $value);
    }
};
