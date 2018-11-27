<?php

namespace LaravelORM;

use LaravelORM\Fields\{
    Field, TimestampField
};
use LaravelORM\LinkFields\LinkField;
use LaravelORM\CompositeFields\CompositeField;
use LaravelORM\Interfaces\{
    IsAField, IsAPrimaryField
};
use LaravelORM\Traits\IsPrepared;
use LaravelORM\Template;

class Schema
{
    use IsPrepared;

    protected $model;
    protected $table;

    protected $fields;
    protected $composites;
    protected $links;

    protected $hasPrimary = false;
    protected $primary;
    protected $index;
    protected $unique;

    protected $fieldManager;

    public function __construct($model)
    {
        $this->model = $model;
        $this->modelName = strtolower((new \ReflectionClass($model))->getShortName());

        $this->fields = config('database.table.fields', []);
        $this->composites = config('database.table.composites', []);
        $this->links = config('database.table.links', []);
        $this->primary = config('database.table.primary');
        $this->index = config('database.table.index', []);
        $this->unique = config('database.table.unique', []);
        $this->defaultFieldConfigs = config('database.fields', []);

        if (config('database.table.timestamps', false)) {
            $this->timestamps();
        }

        $this->fieldManager = new FieldManager($this);
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getModelName()
    {
        return $this->modelName;
    }

    protected function manipulateField($field)
    {
        if ($field instanceof IsAPrimaryField) {
            $this->primary();
        }

        return $field;
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

    public function hasLink($name) {
        return isset($this->getLinks()[$name]);
    }

    public function getLink($name) {
        if ($this->hasLink($name)) {
            return $this->getLinks()[$name];
        } else {
            throw new \Exception($name.' link field does not exist');
        }
    }

    public function getLinks() {
        return $this->links;
    }

    public function hasComposite($name) {
        return isset($this->getComposites()[$name]);
    }

    public function getComposite($name) {
        if ($this->hasComposite($name)) {
            return $this->getComposites()[$name];
        } else {
            throw new \Exception($name.' link field does not exist');
        }
    }

    public function getComposites() {
        return $this->composites;
    }

    public function has($name) {
        return isset($this->all()[$name]);
    }

    public function get($name) {
        if ($this->has($name)) {
            return $this->all()[$name];
        } else {
            throw new \Exception($name.' real or link field does not exist');
        }
    }

    // TODO: Ajouter les conf par défaut pour chaque field s'il n'existe pas => StringField: [length: 256]
    public function set($name, $value) {
        if ($this->has($name)) {
            throw new \Exception('It is not allowed to reset the field '.$name);
        }
        else if (is_string($value)) {
            $value = new $value();
        }

        if ($value instanceof Field) {
            $this->fields[$name] = $this->manipulateField($value)->own($this, $name);
        } else if ($value instanceof CompositeField) {
            $value->own($this, $name);

            $this->composites[$value->getName()] = $this->manipulateField($value);

            foreach ($value->getFields() as $field) {
                if (!$field->isOwned()) {
                    throw new \Exception('The field '.$name.' must be owned by the composed field '.$value->getName());
                }

                $this->fields[$field->getName()] = $this->manipulateField($field);
            }
        } else if ($value instanceof LinkField) {
            if (!$this->preparing && !$this->prepared) {
                throw new \Exception('You cannot set link fields. You must prepare the schema before the schema via the `__schema` method.');
            }

            if ($value->isOwned()) {
                if ($value->getName() !== $name) {
                    throw new \Exception('The link field name must be the same than the given one.');
                }
            } else {
                throw new \Exception('The link field must be owned by a child of the oposite schema.');
            }

            $this->links[$name] = $value;
        } else {
            throw new \Exception('To set a specific field, you have to give a Field object/string');
        }
    }

    public function all() {
        return array_merge(
            $this->fields,
            $this->composites,
            $this->links
        );
    }

    public function getFillableFields() {
        $fillable = [];

        foreach ($this->getFields() as $name => $field) {
            if ($field->hasRule(Field::FILLABLE)) {
                $fillable[] = $name;
            }
        }

        return $fillable;
    }

    public function getVisibleFields() {
        $visible = [];

        foreach ($this->getFields() as $name => $field) {
            if ($field->hasRule(Field::VISIBLE)) {
                $visible[] = $name;
            }
        }

        return $visible;
    }

    protected function prepareSchema() {
        foreach ($this->getComposites() as $field) {
            $field->prepare();
        }
    }

    public function lock() {
        foreach ($this->all() as $field) {
            $field->lock();
        }

        $this->locked = true;
    }

    public function isLocked() {
        return $this->locked;
    }

    public function getPrimary() {
        return $this->primary;
    }

    public function primary(...$fields) {
        if ($this->hasPrimary) {
            throw new \Exception('It is not possible de set primary fields after another');
        }

        $this->hasPrimary = true;

        if (count($fields) === 1) {
            $this->primary = $fields[0];
        }
        else if (count($fields) > 1) {
            $this->primary = $fields;
        }

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
                    else if ($field instanceof CompositeField) {
                        if ($this->get($field->getName()) !== $field) {
                            throw new \Exception('It is not allowed to use external composite fields');
                        }
                        else {
                            foreach ($field->getFields() as $compositeField) {
                                $unique[] = $compositeField;
                            }
                        }
                    }
                    else if ($field instanceof IsAField) {
                        if ($this->get($field->getName()) !== $field) {
                            throw new \Exception('It is not allowed to use external field');
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
                else if ($field instanceof CompositeField) {
                    if ($this->get($field->getName()) !== $field) {
                        throw new \Exception('It is not allowed to use external composite fields');
                    }
                    else {
                        return $this->unique(...$field->getFields());
                    }
                }
                else if ($field instanceof IsAField) {
                    if ($this->get($field->getName()) !== $field) {
                        throw new \Exception('It is not allowed to use external field');
                    }
                    else {
                        $field->unique();
                    }
                }
            }
        }

        return $this;
    }

    public function timestamps() {
        try {
            $this->set($this->model::CREATED_AT ?? 'created_at', TimestampField::new(
                Field::NOT_NULLABLE | Field::VISIBLE
            )->useCurrent());
            $this->set($this->model::UPDATED_AT ?? 'updated_at', TimestampField::new(
                Field::NULLABLE | Field::VISIBLE
            )->useCurrent());
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

    public function generateMigration() {
        $edit = [];
        $add = [];
        $remove = [];
        $link = [];
        $set = [];

        foreach ($this->all() as $field) {
            $attributes = $field->getPreMigration();
            if (count($attributes)) $edit[] = $attributes;

            $attributes = $field->getMigration();
            if (count($attributes)) $add[] = $attributes;

            $attributes = $field->getPostMigration();
            if (count($attributes)) $link[] = $attributes;
        }

        if ($this->hasPrimary) {
            if ($this->primary) {
                $set[] = [
                    'primary' => $this->primary
                ];
            }
        }
        else {
            print('No primary set');
        }

        if (count($this->index)) $set[] = $index;

        foreach ($this->unique as $unique) {
            if (count($this->index)) $set[] = $index;
        }

        $fields = [];

        if (count($edit)) $fields[] = $edit;
        if (count($add)) $fields[] = $add;
        if (count($remove)) $fields[] = $remove;
        if (count($link)) $fields[] = $link;
        if (count($set)) $fields[] = $set;

        return [
            'date' => now(),
            'model' => $this->model,
            'table' => (new $this->model)->getTable(),
            'fields' => $fields
        ];
    }
}
