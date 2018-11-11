<?php

namespace LaravelORM\Fields;

class StringField extends Field
{
    protected $type = 'string';
    protected $length;

    public function __construct(int $length = null)
    {
        parent::__construct();

        if ($length) {
            $this->length($length);
        }
    }

    public function length(int $length) {
        $this->checkLock();

        $this->length = $length;
        $this->properties['length'] = $length;

        return $this;
    }
}
