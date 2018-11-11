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

    public function to(string $model) {
        $this->_checkLock();

        $this->to = $model;

        return $this;
    }

    public function on(string $column) {
        $this->_checkLock();

        $this->on = $column;

        return $this;
    }

    public function from(string $column) {
        $this->_checkLock();

        $this->off = $column;

        return $this;
    }

    public function get($model) {
        return $this->relateToModel($model)->first();
    }

    public function relateToModel($model) {
        return $model->hasMany($this->to, $this->on, $this->off);
    }

    public function scopeWhere($model, ...$args) {
        return $model->where($this->name, ...$args);
    }
}
