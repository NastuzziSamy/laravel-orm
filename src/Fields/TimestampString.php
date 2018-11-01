<?php

namespace NastuzziSamy\Laravel\Fields;

class TimestampField extends Field
{
    protected $type = 'timestamp';

    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }
}
