<?php

namespace NastuzziSamy\Laravel;

use NastuzziSamy\Laravel\Fields\{
    Field, CreatedField, UpdatedField
};

use NastuzziSamy\Laravel\Interfaces\IsAPrimaryField;

class Schema
{
    protected $class;
    protected $fields;
    protected $primary;
    protected $index;
    protected $unique;
    protected $timestamps;
    protected $timestampFields;

    protected $fieldManager;
    protected $lock;

    public function __construct($class)
    {
        $this->class = $class;
        $this->fields = config('database.table.fields', []);
        $this->primary = config('database.table.primary');
        $this->index = config('database.table.index', []);
        $this->unique = config('database.table.unique', []);
        $this->timestamps(config('database.table.timestamps', false));

        $this->generateFieldManager();
    }

    protected function generateFieldManager() {
        $this->fieldManager = new class($this) {
            protected $schema;

            public function __construct($schema) {
                $this->schema = $schema;
            }

            public function __get($name) {
                return $this->schema->getField($name);
            }

            public function __set($name, $value) {
                $this->schema->setField($name, $value);
            }
        };
    }

    public function setField($name, $value) {
        if ($this->hasField($name)) {
            throw new \Exception('It is not allowed to reset the field '.$name);
        }
        else if ($value instanceof string) {
            $this->fields[$name] = new $value($name);
        }
        else if ($value instanceof Field) {
            $this->fields[$name] = $value->fieldName($name);
        }
        else {
            throw new \Exception('To set a specific field, you have to give a Field object/string');
        }

        if ($this->getFields()[$name] instanceof IsAPrimaryField) {
            if ($this->primary) {
                if ($this->primary instanceof Field) {
                    $this->primary = [$this->primary];
                }

                $this->primary[] = $this->getFields()[$name];
            }
            else {
                $this->primary = $this->getFields()[$name];
            }
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
        return array_merge(
            $this->fields,
            $this->timestampFields
        );
    }

    public function getFillableFields() {
        $fillable = [];

        foreach ($this->getFields() as $name => $field) {
            if ($field->isFillable()) {
                $fillable[] = $name;
            }
        }

        return $fillable;
    }

    public function lock() {
        $this->lock = true;
    }

    public function unique(...$fields) {
        $unique = [];

        if (count($fields) > 0) {
            if (count($fields) > 1) {
                foreach ($fields as $field) {
                    if ($field instanceof string) {
                        $unique[] = $this->getField($field);
                    }
                    else if ($field instanceof Field) {
                        if ($this->getField($field->getFieldName()) !== $field) {
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
                else if ($field instanceof Field) {
                    $field->unique();
                }
            }
        }

        return $this;
    }

    public function timestamps(bool $timestamps = true, string $created = 'created_at', string $updated = 'updated_at') {
        $this->timestamps = $timestamps;

        if ($timestamps) {
            $this->timestampFields = [
                $created => new CreatedField($created),
                $updated => new UpdatedField($updated),
            ];
        }
        else {
            $this->timestampFields = [];
        }

        return $this;
    }

    public function __get($name) {
        if ($name === 'fields') {
            return $this->fieldManager;
        }
    }
}
