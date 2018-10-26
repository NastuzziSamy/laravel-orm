<?php

namespace NastuzziSamy\Laravel;

class Fields
{
    private $class;
    private $fields;

    public function __construct($class)
    {
        $this->class = $class;
        $this->fields = config('database.fields', []);
    }

    public function setField($name, Field $field) {
        return ($this->fields[$name] = $field);
    }

    public function hasField($name) {
        return isset($this->fields[$name]);
    }

    public function getField($name) {
        if ($this->hasField($name)) {
            return $this->fields[$name];
        } else {
            throw new \Exception('Field '.$name.' does not exist');
        }
    }
}
