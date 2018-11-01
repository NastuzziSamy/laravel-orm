<?php

namespace NastuzziSamy\Laravel\Fields;

class StringField extends Field
{
    protected $type = 'string';
    private $length;

    public function __construct(int $length = null, string $name = null)
    {
        parent::__construct($name);
    }

    public function getDefaultProperties(): array
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
