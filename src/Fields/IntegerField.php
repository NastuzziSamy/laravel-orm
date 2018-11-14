<?php

namespace LaravelORM\Fields;

class IntegerField extends Field
{
    protected $type = 'integer';
    private $unsigned;

    public function __construct(bool $unsigned = true)
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

    public function getValue($model, $value) {
        return is_null($value) ? $value : (integer) $value;
    }

    public function setValue($model, $value) {
        if (!is_null($value)) {
            $value = (integer) $value;

            if (!$this->unsigned && $value < 0) {
                $value = - $value;
            }
        }

        return $value;
    }
}
