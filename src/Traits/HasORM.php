<?php

namespace LaravelORM\Traits;

use LaravelORM\Schema;
use LaravelORM\CompositeFields\CompositeField;
use LaravelORM\LinkFields\LinkField;
use LaravelORM\Builder;
use Illuminate\Support\Str;

trait HasORM {
    protected static $schema;

    /**
     * Prepare the model during the creation of the object.
     * Add by default fillable fields, visible fields and the primary key.
     *
     * @param mixed $args
     */
    public function __construct(...$args) {
        $return = parent::__construct(...$args);

        $schema = static::getSchema();

        if (!$schema->isLocked()) {
            //throw new \Exception('The schema is not locked and cannot be used correctly');
        }

        $this->fillable = $schema->getFillableFields();
        $this->visible = $schema->getVisibleFields();

        $this->setKeyName($schema->getPrimary());

        return $return;
    }

    /**
     * Generate one time the model schema.
     *
     * @return Schema
     */
    protected static function generateSchema() {
        if (!static::$schema) {
            static::$schema = new Schema(static::class);

            static::__schema(static::$schema, static::$schema->fields);

            static::$schema->prepare();

            # lock doit être appelé par le processus de cloture du chargement
        }

        return static::$schema;
    }

    /**
     * Get the model schema.
     *
     * @return Schema
     */
    public static function getSchema()
    {
        return static::generateSchema();
    }

    /**
     * Return if a field name exists or not.
     * The name could be from one field, link field or composite field.
     *
     * @param  string  $key
     * @return boolean
     */
    public static function hasField(string $key)
    {
        return static::getSchema()->has($key);
    }

    /**
     * Get a field from its name.
     * The name could be from one field, link field or composite field.
     *
     * @param  string  $key
     * @return mixed
     *
     * @throws Exception if the field does not exist.
     */
    public static function getField(string $key)
    {
        return static::getSchema()->get($key);
    }

    /**
     * Return all fields: fields, link fields and composite fields.
     *
     * @return array
     */
    public static function getFields()
    {
        return static::getSchema()->all();
    }

    /**
     * Cast and check a value for a specific key.
     *
     * @param  string $key   Name of the field.
     * @param  mixed  $value
     * @return mixed         The casted value.
     */
    public function cast(string $key, $value) {
        if ($this->hasField($key)) {
            return $this->getField($key)->setValue($this, $value);
        }
    }

    /**
     * Handle dynamically unknown calls.
     * - name(): Returns the relation with the field
     * - name(...$args): Returns a where condition
     * - whereName(...$args): Returns a where condition
     * - andName(...$args): Returns a where condition
     * - orName(...$args): Returns a where condition
     * - andWhereName(...$args): Returns a where condition
     * - orWhereName(...$args): Returns a where condition
     * - castName($value): Returns the casted value (can throw an exception)
     *
     * @param  mixed $method
     * @param  mixed  $args
     * @return mixed
     */
    public function __call($method, $args) {
        $key = Str::snake($method);

        if (static::hasField($key)) {
            $field = static::getField($key);

            if (count($args) === 0) {
                return $field->relationValue($this);
            }
            else {
                return $field->whereValue($this, ...$args);
            }
        } else {
            if (Str::startsWith($key, 'cast_')) {
                return $this->cast(Str::after($key, 'cast_'), $args[0]);
            } else {
                return parent::__call($method, $args);
            }
        }
    }

    /**
     * Get a plain attribute (not a relationship).
     * Override the original method.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);

        // If the attribute has a get mutator, we will call that then return what
        // it returns as the value, which is useful for transforming values on
        // retrieval from the model to a form that is more useful for usage.
        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        // If the attribute exists within the cast array, we will convert it to
        // an appropriate native PHP type dependant upon the associated value
        // given with the key in the pair. Dayle made this comment line up.
        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
        }

        // If the attribute is listed as a date, we will convert it to a DateTime
        // instance on retrieval, which makes it quite convenient to work with
        // date fields without having to create a mutator for each property.
        if (in_array($key, $this->getDates()) &&
            ! is_null($value)) {
            return $this->asDateTime($value);
        }

        $key = Str::snake($key);

        // If the user did not set any custom methods to handle this attribute,
        // we call the field getter.
        if (static::hasField($key)) {
            return static::getField($key)->getValue($this, $value);
        }

        return $value;
    }

    /**
     * Get a relationship.
     * Override the original method.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getRelationValue($key)
    {
        // If the key already exists in the relationships array, it just means the
        // relationship has already been locked, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will lock and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }

        // Check if a composite of link field exist with this name and return the relation.
        if (static::getSchema()->hasComposite($key) || static::getSchema()->hasLink($key)) {
            return $this->getRelationshipFromSchema($key);
        }
    }

    /**
     * Get a relationship value from the schema.
     *
     * @param  string  $method
     * @return mixed
     *
     * @throws \LogicException
     */
    protected function getRelationshipFromSchema($key)
    {
        return tap(static::getSchema()->get($key)->getValue($this, $key), function ($results) use ($key) {
            $this->setRelation($key, $results);
        });
    }

    /**
     * Set a given attribute on the model.
     * Override the original method.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        // First we will check for the presence of a mutator for the set operation
        // which simply lets the developers tweak the attribute as it is set on
        // the model, such as "json_encoding" an listing of data for storage.
        if ($this->hasSetMutator($key)) {
            return $this->setMutatedAttributeValue($key, $value);
        }

        // If an attribute is listed as a "date", we'll convert it from a DateTime
        // instance into a form proper for storage on the database tables using
        // the connection grammar's date format. We will auto set the values.
        elseif ($value && $this->isDateAttribute($key)) {
            $this->attributes[$key] = $this->fromDateTime($value);

            return $this;
        }

        if ($this->isJsonCastable($key) && ! is_null($value)) {
            $this->attributes[$key] = $this->castAttributeAsJson($key, $value);

            return $this;
        }

        // If this attribute contains a JSON ->, we'll set the proper value in the
        // attribute's underlying array. This takes care of properly nesting an
        // attribute in the array's value in the case of deeply nested items.
        if (Str::contains($key, '->')) {
            return $this->fillJsonAttribute($key, $value);
        }

        $key = Str::snake($key);

        // Check if the field exists to cast the value.
        if (static::hasField($key)) {
            $field = static::getField($key);

            // A composite field cannot set an attribute with its name.
            if ($field instanceof CompositeField || $field instanceof LinkField) {
                // Call the method to set the composed fields.
                $field->setValue($this, $value);

                return $this;
            }

            // If the field is not fillable, throw an exception.
            if (!$this->isFillable($key)) {
                throw new \Exception('The field '.$key.' is not fillable');
            }

            $value = $field->setValue($this, $value);
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Get the relation value for a specific key.
     *
     * @param  string $key
     * @return mixed
     */
    public function relation(string $key) {
        return static::getField($key)->relationValue($this);
    }

    /**
     * Create a new Eloquent query builder for the model.
     * Override the original method.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }
}
