<?php

namespace LaravelORM\Fields;

class BooleanField extends Field
{
    protected $type = 'boolean';

    public function __construct(bool $boolean = true)
    {
        parent::__construct();

        $this->default($boolean);
    }

    public function default(bool $value = null) {
        return parent::default($value);
    }

    protected function castValue($value) {
        return is_null($value) ? $value : (bool) $value;
    }
}
