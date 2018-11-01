<?php

namespace NastuzziSamy\Laravel\Fields;

class BelongsField extends PointerField
{
    protected $to;
    protected $on;

    public function __construct(string $model = null, string $column = 'id', string $name = null)
    {
        $this->to = $model;
        $this->on = $column;

        parent::__construct($name);
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

    public function lock(string $name) {
        if (!($this->to && $this->on)) {
            throw new \Exception('Related model needed. Set it by calling `to`');
        }

        return parent::lock($name);
    }

    public function get($model) {
        $this->call($model)->first();
    }

    public function call($model, ...$args) {
        if (count($args) === 0) {
            return $model->belongsTo($this->to, $this->columnName, $this->on);
        }
        else {
            return $model->where($this->columnName, ...$args);
        }
    }
}
