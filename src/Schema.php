<?php

namespace LaravelORM;

use LaravelORM\Fields\{
    Field, TimestampField
};
use LaravelORM\CompositeFields\CompositeField;

use LaravelORM\Interfaces\{
    IsAField, IsAPrimaryField
};

class Schema
{
    protected $model;
    protected $fields;
    protected $composites;
    protected $fakes;
    protected $primary;
    protected $index;
    protected $unique;
    protected $timestamps;
    protected $timestampFields;

    protected $fieldManager;
    protected $locked;

    public function __construct($model)
    {
        $this->model = $model;
        $this->fields = config('database.table.fields', []);
        $this->composites = config('database.table.composites', []);
        $this->fakes = config('database.table.fakes', []);
        $this->primary = config('database.table.primary');
        $this->index = config('database.table.index', []);
        $this->unique = config('database.table.unique', []);
        $this->defaultFieldConfigs = config('database.fields', []);

        if (config('database.table.timestamps', false)) {
            $this->timestamps();
        }

        $this->fieldManager = new FieldManager($this);
    }

    protected function _manipulateField($field) {
        if ($field instanceof IsAPrimaryField) {
            $this->primary($field);
        }

        return $field;
    }

    // TODO: Ajouter les conf par défaut pour chaque field s'il n'existe pas => StringField: [length: 256]
    public function setField($name, $value) {
        if ($this->hasField($name)) {
            throw new \Exception('It is not allowed to reset the field '.$name);
        }
        else if (is_string($value)) {
            $value = new $value();
        }

        if ($value instanceof Field) {
            $this->fields[$name] = $this->_manipulateField($value->lock($name));
        }
        else if ($value instanceof CompositeField) {
            $value->lock($name);
            $this->composites[$value->getName()] = $value;

            foreach ($value->getFields() as $field) {
                $this->fields[$field->getName()] = $this->_manipulateField($field);
            }

            foreach ($value->getFakes() as $fake) {
                $this->fakes[$fake->getName()] = $this->_manipulateField($fake);
            }
        }
        else {
            throw new \Exception('To set a specific field, you have to give a Field object/string');
        }
    }

    public function hasField($name) {
        return isset($this->getFields()[$name]);
    }

    public function getField($name) {
        if ($this->hasField($name)) {
            return $this->getFields()[$name];
        } else {
            throw new \Exception($name.' field does not exist');
        }
    }

    public function getFields() {
        return $this->fields;
    }

    public function hasFake($name) {
        return isset($this->getFakes()[$name]);
    }

    public function getFake($name) {
        if ($this->hasFake($name)) {
            return $this->getFakes()[$name];
        } else {
            throw new \Exception($name.' fake field does not exist');
        }
    }

    public function getFakes() {
        return $this->fakes;
    }

    public function hasFieldOrFake($name) {
        return isset($this->getFieldsAndFakes()[$name]);
    }

    public function getFieldOrFake($name) {
        if ($this->hasFieldOrFake($name)) {
            return $this->getFieldsAndFakes()[$name];
        } else {
            throw new \Exception($name.' real or fake field does not exist');
        }
    }

    public function getFieldsAndFakes() {
        return array_merge(
            $this->fields,
            $this->fakes
        );
    }

    public function getFillableFields() {
        $fillable = [];

        foreach ($this->getFields() as $name => $field) {
            if ($field->fillable) {
                $fillable[] = $name;
            }
        }

        return $fillable;
    }

    public function getVisibleFields() {
        $visible = [];

        foreach ($this->getFields() as $name => $field) {
            if ($field->visible) {
                $visible[] = $name;
            }
        }

        return $visible;
    }

    public function lock() {
        $this->locked = true;
    }

    public function primary(...$fields) {
        if ($this->primary) {
            throw new \Exception('It is not possible de set primary fields after another');
        }

        $this->primary = $fields;

        return $this;
    }

    public function unique(...$fields) {
        $unique = [];

        if (count($fields) > 0) {
            if (count($fields) > 1) {
                foreach ($fields as $field) {
                    if ($field instanceof string) {
                        $unique[] = $this->getField($field);
                    }
                    else if ($field instanceof IsAField) {
                        if ($this->getFieldOrFake($field->getName()) !== $field) {
                            throw new \Exception('It is not allowed to use external field as unique');
                        }
                        else {
                            $unique[] = $field;
                        }
                    }
                }

                $this->unique[] = $unique;
            }
            else {
                $field = $fields[0];

                if ($field instanceof string) {
                    $this->getField($field)->unique();
                }
                else if ($field instanceof IsAField) {
                    $field->unique();
                }
            }
        }

        return $this;
    }

    public function timestamps() {
        try {
            $this->setField($this->model::CREATED_AT ?? 'created_at', TimestampField::nullable());
            $this->setField($this->model::UPDATED_AT ?? 'updated_at', TimestampField::nullable());
        } catch (\Exception $e) {
            throw new \Exception('Can not set timestamps. Maybe already set ?');
        }

        // // We make sure Laravel knows we have timestamps
        // $this->model->timestamps = true;

        return $this;
    }

    public function __get($name) {
        if ($name === 'fields') {
            return $this->fieldManager;
        }
    }
}
