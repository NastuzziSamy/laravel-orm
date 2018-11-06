<?php

namespace LaravelORM\Fields;

class TimestampField extends Field
{
    protected $type = 'timestamp';

    public function _getDefaultProperties(): array
    {
        return [];
    }
}
