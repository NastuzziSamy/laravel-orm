<?php

namespace NastuzziSamy\Laravel\Fields;

class StringField extends Field
{
    protected $type = 'string';
    private $length;

    public function __construct(int $length = null)
    {
        parent::__construct();
    }

    public function _getDefaultProperties(): array
    {
        return [
            'length' => $this->length
        ];
    }

    public function length(int $length = null) {
        $this->length = $length;

        return $this->column->length($length);
    }
}
