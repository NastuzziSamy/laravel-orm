<?php

namespace LaravelORM\CompositeFields;

use LaravelORM\Fields\IntegerField;

class BelongsField extends CompositeField
{
    protected $fields = [
        IntegerField::class
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

        $this->fields[0]->unsigned();
    }

    protected function setName($value) {
        $name = $this->generateFieldName($value, $this->identifier, $this->delimiter);

        return parent::setName($value);
    }

    public function identifier($identifier, $delimiter = null) {
        $this->identifier = $identifier;
        $this->delimiter = $delimiter ?? $this->delimiter;

        return $this;
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
            throw new \Exception('Related model settings needed. Set it by calling `to` method');
        }

        $this->fields[0]->lock($this->generateFieldName($name, $this->identifier, $this->delimiter));

        return parent::lock($name);
    }

    protected function generateFieldName(string $name = null, string $identifier = null, string $delimiter = null) {
        return $name.$delimiter.$identifier;
    }

    public function getValue($model, $value) {
        return $this->relationValue($model)->first();
    }

    public function setValue($model, $value) {
        $model->setAttribute($this->fields[0]->getName(), $value->getKey());
        $model->setRelation($this->name, $value);
    }

    public function relationValue($model) {
        return $model->belongsTo($this->to, $this->fields[0]->getName(), $this->on);
    }

    public function whereValue($query, ...$args) {
        if (count($args) > 1) {
            list($operator, $value) = $args;
        }
        else {
            $operator = '=';
            $value = $args[0] ?? null;
        }

        if (is_object($value)) {
            $value = $value->getKey();
        }
        else if (!is_null($value)) {
            $value = (integer) $value;
        }

        return $query->where($this->fields[0]->getName(), $operator, $value);
    }

    public function getPreMigration() {
        return [];
    }

    public function getMigration() {
        return [];
    }

    public function getPostMigration() {
        return [
            'foreign' => $this->fields[0]->getName(),
            'references' => $this->on,
            'on' => (new $this->to)->getTable(),
        ];
    }
}
