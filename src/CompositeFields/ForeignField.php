<?php

namespace LaravelORM\CompositeFields;

use LaravelORM\Fields\IntegerField;
use LaravelORM\LinkFields\HasManyField;
use Illuminate\Support\Str;

class ForeignField extends CompositeField
{
    protected $fields = [
        IntegerField::class
    ];
    protected $links = [
        HasManyField::class
    ];

    protected $to;
    protected $on;
    protected $delimiter;
    protected $identifier;
    protected $reversedName;


    public function __construct(string $model = null, string $column = 'id', string $identifier = 'id', string $delimiter = '_')
    {
        $this->to = $model;
        $this->on = $column;

        $this->identifier = $identifier;
        $this->delimiter = $delimiter;

        parent::__construct();

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

    public function to(string $model, string $reversedName = null) {
        $this->checkLock();

        $this->to = $model;
        $this->reversedName = $this->reversedName ?? $reversedName;

        return $this;
    }

    public function on(string $column) {
        $this->checkLock();

        $this->on = $column;

        return $this;
    }

    protected function ownFields() {
        if (!($this->to && $this->on)) {
            throw new \Exception('Related model settings needed. Set it by calling `to` method');
        }

        $this->fields[0]->own($this, $this->generateFieldName());

        return $this;
    }

    protected function prepareComposite() {
        $this->links[0]->own($this, $this->generateLinkName())
            ->to($this->getOwner()->getModel())
            ->from($this->on)
            ->on($this->fields[0]->getName());

        $this->to::getSchema()->set($this->links[0]->getName(), $this->links[0]);

        return $this;
    }

    public function reversedName(string $reversedName) {
        $this->checkLock();

        $this->reversedName = $reversedName;

        return $this;
    }

    protected function generateFieldName() {
        return $this->name.$this->delimiter.$this->identifier;
    }

    protected function generateLinkName() {
        if ($this->reversedName) {
            return $this->reversedName;
        } else {
            return Str::plural($this->getOwner()->getModelName());
        }
    }

    public function getValue($model, $value) {
        return $this->relationValue($model)->first();
    }

    public function setValue($model, $value) {
        $model->setAttribute($this->fields[0]->getName(), $value->{$this->on});
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
            $value = $value->{$this->on};
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
            'link' => $this->fields[0]->getName(),
            'references' => $this->on,
            'on' => (new $this->to)->getTable(),
        ];
    }
}
