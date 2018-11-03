<?php

namespace NastuzziSamy\Laravel\Fields;

class IntegerField extends Field
{
    protected $type = 'integer';
    private $unsigned;

    public function __construct(bool $unsigned = false)
    {
        $this->unsigned = $unsigned;

        parent::__construct();
    }

    protected function _getDefaultProperties(): array
    {
        return [
            'unsigned' => $this->unsigned
        ];
    }

    protected function unsigned(bool $unsigned = true) {
        $this->_checkLock();

        $this->unsigned = $unsigned;

        return $this->column->unsigned($unsigned);
    }
}
