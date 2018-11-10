<?php

namespace LaravelORM\Fields;

class IntegerField extends Field
{
    protected $type = 'integer';
    private $unsigned;

    public function __construct(bool $unsigned = false)
    {
        parent::__construct();

        $this->unsigned($unsigned);
    }

    protected function unsigned(bool $unsigned = true) {
        $this->_checkLock();

        $this->unsigned = $unsigned;
        $this->properties['unsigned'] = $unsigned;
    }
}
