<?php

namespace NastuzziSamy\Laravel\CompositeFields;

use NastuzziSamy\Laravel\Traits\StaticCallable;

abstract class CompositeField
{
    use StaticCallable;

    protected $name;
    protected $fields = [];
    protected $fakes = [];
    protected $uniques = [];

    protected $locked = false;

    public function __construct()
    {
        foreach ($this->fields as $key => $value) {
            if (is_string($value)) {
                $this->fields[$key] = new $value;
            }
        }

        foreach ($this->fakes as $key => $value) {
            if (is_string($value)) {
                $this->fakes[$key] = new $value;
            }
        }
    }

    protected function _name($value) {
        $this->_checkLock();

        $this->name = $value;
    }

    public function getName() {
        return $this->name;
    }

    protected function unique() {
        $this->_checkLock();

        $this->unique[] = $this->getFields();
    }

    protected function getFields()
    {
        return array_values($this->fields);
    }

    protected function getFakes()
    {
        return array_values($this->fakes);
    }

    protected function getUniques()
    {
        return $this->uniques;
    }

    protected function _checkLock() {
        if ($this->locked) {
            throw new \Exception('The composite field is locked, nothing can change');
        }
    }

    protected function lock(string $name) {
        $this->_name($name);

        $this->locked = true;
    }
}
