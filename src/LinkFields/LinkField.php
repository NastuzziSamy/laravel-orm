<?php

namespace LaravelORM\LinkFields;

use LaravelORM\Interfaces\IsAField;
use LaravelORM\Traits\IsOwnedAndLocked;

abstract class LinkField implements IsAField
{
    use IsOwnedAndLocked;


    public function __construct() {}

    public static function new(...$args) {
        return new static(...$args);
    }

    public function call($model, ...$args) {
        if (count($args) === 0) {
            return $this->relatedToModel($model);
        }
        else {
            return $this->scopeWhere($model, ...$args);
        }
    }

    abstract public function getValue($model, $value);
    abstract public function setValue($model, $value);
    abstract public function relationValue($model);
    abstract public function whereValue($model, ...$args);

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
