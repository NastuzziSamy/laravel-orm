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
            $this->unsigned($unsigned);
        }
    }

    protected function length(int $length) {
        $this->_checkLock();

        $this->length = $length;
        $this->properties['length'] = $length;
    }
}
