<?php

namespace LaravelORM\Traits;

use LaravelORM\Schema;

Trait LaravelORM {
    public function __construct(...$args) {
        $schema = self::getSchema();

        $this->fillable = $schema->getFillableFields();
        $this->visible = $schema->getVisibleFields();

        return parent::__construct(...$args);
    }

    protected static function generateSchema() {
        if (!self::$schema) {
            self::$schema = new Schema(self::class);

            self::__schema(self::$schema, self::$schema->fields);

            self::$schema->lock();
        }

        return self::$schema;
    }

    public static function getSchema()
    {
        return self::generateSchema();
    }

    public function __call($name, $args) {
        if (self::hasFieldOrFake($name)) {
            return self::getFieldOrFake($name)->call($this, ...$args);
        } else {
            return parent::__call($name, $args);
        }
    }

    public function __get($name) {
        if (self::hasFieldOrFake($name)) {
            return self::getFieldOrFake($name)->get($this);
        } else {
            return parent::__get($name);
        }
    }
}
