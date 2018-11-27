<?php

namespace LaravelORM\LinkFields;

class HasManyField extends LinkField
{
    protected $from;
    protected $to;
    protected $on;

    public function _construct(string $model = null, string $column = 'id', string $fromColumn = null)
    {
        $this->from = $fromColumn;
        $this->to = $model;
        $this->on = $column;

        parent::_construct();
    }

    public function to(string $model) {
        $this->checkLock();

        $this->to = $model;

        return $this;
    }

    public function on(string $column) {
        $this->checkLock();

        $this->on = $column;

        return $this;
    }

    public function from(string $column) {
        $this->checkLock();

        $this->from = $column;

        return $this;
    }

    public function getValue($model, $value) {
        return $this->relationValue($model)->first();
    }

    public function setValue($model, $value) {
        return $this->relationValue($model)->sync($value);
    }

    public function relationValue($model) {
        return $model->hasMany($this->to, $this->on, $this->from);
    }

    public function whereValue($model, ...$args) {
        //return $model->where($this->name, ...$args);
    }
}
