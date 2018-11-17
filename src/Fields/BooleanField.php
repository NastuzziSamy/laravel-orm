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

    public function castValue($value) {
        return (boolean) $value;
    }
}
