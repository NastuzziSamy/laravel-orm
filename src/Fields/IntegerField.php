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

    public function unsigned(bool $unsigned = true) {
        $this->checkLock();

        $this->unsigned = $unsigned;
        $this->properties['unsigned'] = $unsigned;

        return $this;
    }
}
