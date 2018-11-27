<?php

namespace LaravelORM\Fields;

class BooleanField extends Field
{
    protected $type = 'boolean';

    protected function castValue($value) {
        return is_null($value) ? $value : (bool) $value;
    }
}
