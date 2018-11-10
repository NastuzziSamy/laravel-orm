<?php

namespace LaravelORM\FakeFields;

class HasManyField extends FakeField
{
    protected $off;
    protected $to;
    protected $on;

    public function __construct(string $model = null, string $column = 'id', string $fromColumn = null)
    {
        $this->off = $fromColumn;
        $this->to = $model;
        $this->on = $column;

        parent::__construct();
    }

    protected function to(string $model) {
        $this->_checkLock();

        $this->to = $model;
    }

    protected function on(string $column) {
        $this->_checkLock();

        $this->on = $column;
    }

    protected function from(string $column) {
        $this->_checkLock();

        $this->off = $column;
    }

    public function get($model) {
        $this->relateToModel($model)->first();
    }

    public function getFieldNames() {
        return [$this->off];
    }

    public function relateToModel($model) {
        return $model->hasMany($this->to, $this->on, $this->off);
    }

    public function scopeWhere($model, ...$args) {
        return $model->where($this->name, ...$args);
    }
}
