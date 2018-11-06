<?php

namespace LaravelORM\CompositeFields;

use LaravelORM\Fields\IntegerField;
use LaravelORM\FakeFields\BelongsToField;

class BelongsField extends CompositeField
{
    protected $fields = [
        IntegerField::class
    ];
    protected $fakes = [
        BelongsToField::class
    ];

    protected $to;
    protected $on;
    protected $delimiter;
    protected $identifier;

    public function __construct(string $model = null, string $column = 'id', string $name = null, string $identifier = 'id', string $delimiter = '_')
    {
        $this->to = $model;
        $this->on = $column;

        $this->identifier = $identifier;
        $this->delimiter = $delimiter;

        parent::__construct($name);
    }

    protected function _name($value) {
        $this->_checkLock();

        $name = $this->_generateFieldName($value, $this->identifier, $this->delimiter);

        $this->fakes[0]->from($name);

        return parent::_name($value);
    }

    protected function identifier($identifier, $delimiter = null) {
        $this->identifier = $identifier;
        $this->delimiter = $delimiter ?? $this->delimiter;

        return $this;
    }

    protected function to(string $model) {
        $this->_checkLock();

        $this->to = $model;
    }

    protected function on(string $column) {
        $this->_checkLock();

        $this->on = $column;
    }

    protected function lock(string $name) {
        if (!($this->to && $this->on)) {
            throw new \Exception('Related model settings needed. Set it by calling `to` method');
        }

        $response = parent::lock($name);

        $this->fields[0]->to($this->to)->on($this->on)->lock($this->_generateFieldName($name, $this->identifier, $this->delimiter));
        $this->fakes[0]->to($this->to)->on($this->on)->lock($name);

        return $response;
    }

    protected function _generateFieldName(string $name = null, string $identifier = null, string $delimiter = null) {
        return $name.$delimiter.$identifier;
    }
}
