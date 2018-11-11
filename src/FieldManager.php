<?php

namespace LaravelORM;

use LaravelORM\Fields\Field;

class FieldManager{
    protected $schema;

    public function __construct($schema) {
        $this->schema = $schema;
    }

    public function __get($name) {
        return $this->schema->get($name);
    }

    public function __set($name, $value) {
        $this->schema->set($name, $value);
    }

    public function __call($method, $args) {
        $field = new class($method, $args[0]) extends Field {
            public function __construct($type, $name) {
                $this->type = $type;
                $this->name = $name;

                parent::__construct();
            }
        };

        $this->__set($args[0], $field);

        return $field;
    }
};
