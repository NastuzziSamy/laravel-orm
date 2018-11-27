<?php

namespace LaravelORM\CompositeFields;

use LaravelORM\Interfaces\IsAField;
use LaravelORM\Traits\{
    IsOwnedAndLocked, IsPrepared
};

abstract class CompositeField implements IsAField
{
    use IsOwnedAndLocked, IsPrepared {
        IsOwnedAndLocked::own as protected ownComposite;
    }

    protected $fields = [];
    protected $links = [];
    protected $uniques = [];


    public function __construct()
    {
        foreach ($this->fields as $key => $value) {
            if (is_string($value)) {
                $this->fields[$key] = new $value;
            }
        }

        foreach ($this->links as $key => $value) {
            if (is_string($value)) {
                $this->links[$key] = new $value;
            }
        }
    }

    public static function new(...$args) {
        return new static(...$args);
    }

    public function unique() {
        $this->checkLock();

        $this->unique[] = $this->getFields();

        return $this;
    }

    public function own($owner, string $name) {
        $return = $this->ownComposite($owner, $name);

        $this->ownFields();

        return $return;
    }

    abstract protected function prepareComposite();

    public function getFields()
    {
        return array_values($this->fields);
    }

    public function getUniques()
    {
        return $this->uniques;
    }

    public function getValue($mode, $value) {
        return $value;
    }

    public function setValue($mode, $value) {
        $model->setAttribute($this->name, $value);

        return $this;
    }

    public function relationValue($model) {
        return $this;
    }

    public function whereValue($model, ...$args) {
        return $model->where($this->name, ...$args);
    }
}
