<?php

namespace NastuzziSamy\Laravel\Fields;

class IntegerField extends Field
{
    protected $type = 'integer';
    private $unsigned;

    public function __construct(bool $unsigned = false, string $name = null)
    {
        $this->unsigned = $unsigned;

        parent::__construct($name);
    }

    public function getDefaultProperties(): array
    {
        return [
            'unsigned' => $this->unsigned
        ];
    }

    public function unsigned(bool $unsigned = true) {
        $this->unsigned = $unsigned;

        return $this->column->unsigned($unsigned);
    }
}
