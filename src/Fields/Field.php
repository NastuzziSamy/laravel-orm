<?php

namespace NastuzziSamy\Laravel\Fields;

abstract class Field
{
    protected $name;
    protected $fieldName;
    protected $type;
    protected $default;

    protected $fillable = true;
    protected $unique = false;
    protected $nullable = false;
    protected $increments = false;

    public function __construct($name = null)
    {
        $this->fieldName($name);
    }

    public function fieldName($name)
    {
        $this->fieldName = $this->fieldName ?? $name;
        $this->name = $this->name ?? $name;

        return $this;
    }

    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    public function fillable($fillable = true)
    {
        $this->fillable = $fillable;

        return $this;
    }

    public function unique($unique = true)
    {
        $this->unique = $unique;

        return $this;
    }

    public function nullable($nullable = true)
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function increments()
    {
        $this->increments = $increments;

        return $this;
    }

    public function setDefault($value)
    {
        $this->default = $value;

        return $this;
    }

    public function getValue($model)
    {
        return $model->getAttribute($this->fieldName);
    }

    public function createTableField($table) {
        return $table->{$this->type}($this->fieldName);
    }

    public function getFieldName() {
        return $this->fieldName;
    }

    public function getName() {
        return $this->name;
    }

    public function isFillable() {
        return $this->fillable;
    }

    public function isUnique() {
        return $this->unique;
    }

    public function generateTable() {

    }
}
